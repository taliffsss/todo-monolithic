<?php

namespace App\Http\Controllers\Api;

use App\Enum\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Task\TaskStoreRequest;
use App\Http\Requests\Task\TaskUpdateRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected TaskService $taskService) {}

    public function index(Request $request): ResourceCollection
    {
        $this->authorize('viewAny', Task::class);

        $filters = array_filter($request->all(), function ($value) {
            return $value !== null && $value !== '';
        });

        // Add default sorting if not provided
        if (! isset($filters['sortBy'])) {
            $filters['sortBy'] = 'created_at';
        }
        if (! isset($filters['sortDirection'])) {
            $filters['sortDirection'] = 'desc';
        }

        $tasks = $this->taskService->list($filters);

        return TaskResource::collection($tasks);
    }

    public function store(TaskStoreRequest $request): JsonResource
    {
        $this->authorize('create', Task::class);

        $task = $this->taskService->create($request->validated());

        return new TaskResource($task);
    }

    public function show(int $id): JsonResource
    {
        $task = Task::findOrFail($id);
        $this->authorize('view', $task);

        return new TaskResource($task->load(['tags', 'attachments']));
    }

    public function update(TaskUpdateRequest $request, int $id): JsonResource
    {
        $this->authorize('update', Task::findOrFail($id));

        $task = $this->taskService->update($id, $request->validated());

        return new TaskResource($task);
    }

    public function destroy(int $id)
    {
        $this->authorize('delete', Task::findOrFail($id));

        $this->taskService->delete($id);

        return response()->noContent();
    }

    public function complete(Request $request, int $id): JsonResource
    {
        $this->authorize('complete', Task::findOrFail($id));

        $task = $this->taskService->toggleComplete($id);

        return new TaskResource($task);
    }

    public function incomplete(int $id): JsonResource
    {
        $this->authorize('complete', Task::findOrFail($id));
        $task = $this->taskService->toggleComplete($id);

        return new TaskResource($task);
    }

    public function archive(int $id): JsonResource
    {
        $task = $this->taskService->toggleArchive($id, TaskStatus::ARCHIVED);

        return new TaskResource($task);
    }

    public function restore(int $id): JsonResource
    {
        $task = $this->taskService->toggleArchive($id, TaskStatus::RESTORE);

        return new TaskResource($task);
    }
}
