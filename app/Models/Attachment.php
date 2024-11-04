<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'file_name',
        'file_path',
        'file_type',
        'mime_type',
        'size',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_size',
        'url',
    ];

    /**
     * Get the task that owns the attachment.
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Get the formatted file size.
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Get the URL for the attachment.
     */
    public function getUrlAttribute(): string
    {
        // Using the Storage facade to generate a URL
        return Storage::url($this->file_path);
    }

    /**
     * Get the file extension.
     */
    public function getExtensionAttribute(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Determine if the attachment is an image.
     */
    public function getIsImageAttribute(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Determine if the attachment is a video.
     */
    public function getIsVideoAttribute(): bool
    {
        return $this->file_type === 'video';
    }

    /**
     * Determine if the attachment is a document.
     */
    public function getIsDocumentAttribute(): bool
    {
        return $this->file_type === 'document';
    }

    /**
     * Get the full disk path of the file.
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->file_path);
    }
}
