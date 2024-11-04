<?php

namespace App\Services;

use App\Enum\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TaskService
{
    public function list(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Task::query()->where('user_id', auth()->id());

        // Only apply filters if they have non-empty values
        if (! empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (isset($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->whereNotNull('completed_at');
            } elseif ($filters['status'] === 'todo') {
                $query->whereNull('completed_at');
            }
        }

        if (isset($filters['archived']) && $filters['archived'] === 'true') {
            $query->whereNotNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }

        if (! empty($filters['dateFrom'])) {
            $query->where('due_date', '>=', $filters['dateFrom']);
        }

        if (! empty($filters['dateTo'])) {
            $query->where('due_date', '<=', $filters['dateTo']);
        }

        // Only apply sorting if valid values are provided
        $sortBy = in_array($filters['sortBy'] ?? '', [
            'created_at',
            'due_date',
            'priority',
            'completed_at',
        ]) ? $filters['sortBy'] : 'created_at';

        $sortDirection = in_array(strtolower($filters['sortDirection'] ?? ''), ['asc', 'desc'])
            ? strtolower($filters['sortDirection'])
            : 'desc';

        $query->orderBy($sortBy, $sortDirection);

        // Handle pagination
        $page = max(1, intval($filters['page'] ?? 1));
        $perPage = max(1, min(50, intval($perPage))); // Limit max items per page to 50

        return $query->with(['tags', 'attachments'])->paginate(
            perPage: $perPage,
            page: $page
        );
    }

    public function create(array $data): Task
    {
        return DB::transaction(function () use ($data) {
            $task = Task::create([
                'user_id' => auth()->id(),
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? 'normal',
                'due_date' => $data['due_date'] ?? null,
                'task_order' => $this->getNextTaskOrder(),
            ]);

            if (! empty($data['tags'])) {
                $this->handleTags($task->id, $data['tags']);
            }

            if (! empty($data['attachments'])) {
                $this->handleAttachments($task->id, $data['attachments']);
            }

            return $task->load(['tags', 'attachments']);
        });
    }

    public function update(int $id, array $data): Task
    {
        $task = Task::findOrFail($id);

        return DB::transaction(function () use ($task, $data) {
            $task->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'] ?? $task->priority,
                'due_date' => $data['due_date'] ?? $task->due_date,
            ]);

            if (isset($data['tags'])) {
                $this->handleTags($task->id, $data['tags']);
            }

            if (! empty($data['attachments'])) {
                $this->handleAttachments($task->id, $data['attachments']);
            }

            return $task->load(['tags', 'attachments']);
        });
    }

    public function toggleComplete(int $id): Task
    {
        $task = Task::findOrFail($id);
        $task->update([
            'completed_at' => $task->completed_at ? null : now(),
        ]);

        return $task->load(['tags', 'attachments']);
    }

    public function toggleArchive(int $id, TaskStatus $status): Task
    {
        $task = Task::findOrFail($id);
        $task->update([
            'archived_at' => $status === TaskStatus::RESTORE ? null : now(),
        ]);

        return $task->load(['tags', 'attachments']);
    }

    public function delete(int $id): void
    {
        $task = Task::findOrFail($id);
        DB::transaction(function () use ($task) {
            foreach ($task->attachments as $attachment) {
                Storage::delete($attachment->file_path);
            }

            $task->delete();
        });
    }

    public function reorder(array $tasksOrder): void
    {
        DB::transaction(function () use ($tasksOrder) {
            foreach ($tasksOrder as $order) {
                Task::where('id', $order['id'])
                    ->where('user_id', auth()->id())
                    ->update(['task_order' => $order['order']]);
            }
        });
    }

    private function handleTags(int $id, array $tagNames): void
    {
        $task = Task::findOrFail($id);
        $tags = collect($tagNames)->map(function ($tagName) {
            return Tag::firstOrCreate(['name' => $tagName]);
        });

        $task->tags()->sync($tags->pluck('id'));
    }

    private function getNextTaskOrder(): int
    {
        return Task::where('user_id', auth()->id())->max('task_order') + 1;
    }

    /**
     * Handle file attachments for a task
     */
    private function handleAttachments(int $id, array $files): void
    {
        $task = Task::findOrFail($id);
        foreach ($files as $file) {
            $path = $file->store('attachments/'.$task->id);

            $task->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $this->determineFileType($file),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    /**
     * Determine the type of file being uploaded
     */
    private function determineFileType($file): string
    {
        $mime = $file->getMimeType();

        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return 'document';
    }
}
