<?php

namespace App\Services;

use App\Models\Task;

class AttachmentService
{
    public function download(int $id, mixed $attachmentId)
    {
        $task = Task::findOrFail($id);
    }
}
