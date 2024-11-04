<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'due_date' => $this->when($this->due_date, fn () => $this->due_date->format('Y-m-d')),
            'task_order' => $this->task_order,
            'completed_at' => $this->when($this->completed_at, fn () => $this->completed_at->format('Y-m-d H:i:s')),
            'archived_at' => $this->when($this->archived_at, fn () => $this->archived_at->format('Y-m-d H:i:s')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
