<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Storage;

class TaskObserver
{
    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        // Delete all attachments when task is deleted
        foreach ($task->attachments as $attachment) {
            Storage::delete($attachment->file_path);
        }
    }
}
