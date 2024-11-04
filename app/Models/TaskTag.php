<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskTag extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'task_id',
        'tag_id',
    ];

    /**
     * Get the task for this pivot.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the tag for this pivot.
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }
}
