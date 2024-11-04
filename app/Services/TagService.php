<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagService
{
    /**
     * Get all tags for authenticated user
     */
    public function list(): Collection
    {
        return Tag::whereHas('tasks', function ($query) {
            $query->where('user_id', auth()->id());
        })->get();
    }

    /**
     * Create a new tag
     */
    public function create(array $data): Tag
    {
        return DB::transaction(function () use ($data) {
            return Tag::firstOrCreate([
                'name' => $data['name'],
            ]);
        });
    }

    /**
     * Update the specified tag
     */
    public function update(Tag $tag, array $data): Tag
    {
        return DB::transaction(function () use ($tag, $data) {
            $tag->update([
                'name' => $data['name'],
            ]);

            return $tag->fresh();
        });
    }

    /**
     * Delete the specified tag
     */
    public function delete(Tag $tag): bool
    {
        return DB::transaction(function () use ($tag) {
            // Only delete if tag is not used by other users
            if (! $tag->tasks()->where('user_id', '!=', auth()->id())->exists()) {
                return $tag->delete();
            }

            return false;
        });
    }

    /**
     * Find tags by name
     */
    public function findByNames(array $names): Collection
    {
        return Tag::whereIn('name', $names)->get();
    }

    /**
     * Attach tags to a task
     */
    public function attachToTask($task, array $tagNames): void
    {
        $tags = collect($tagNames)->map(function ($name) {
            return Tag::firstOrCreate(['name' => $name]);
        });

        $task->tags()->sync($tags->pluck('id'));
    }

    /**
     * Detach a tag from a task
     */
    public function detachFromTask($task, Tag $tag): void
    {
        $task->tags()->detach($tag->id);
    }

    /**
     * Search tags by name
     */
    public function search(string $query): Collection
    {
        return Tag::where('name', 'like', "%{$query}%")
            ->whereHas('tasks', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->limit(10)
            ->get();
    }
}
