<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TaskAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected AttachmentService $attachmentService) {}

    public function download(int $taskId, mixed $attachmentId)
    {
        $task = Task::findOrFail($taskId);

        return Storage::download($attachment->file_path, $attachment->file_name);
    }
}
