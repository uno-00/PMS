<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasUuid;

    protected $fillable = [
        'documentable_type', 'documentable_id', 'module', 'category', 'disk', 'path',
        'original_filename', 'mime_type', 'size', 'version', 'checksum', 'uploaded_by',
        'metadata', 'retain_until',
    ];

    protected $casts = [
        'metadata' => 'array',
        'retain_until' => 'datetime',
        'size' => 'integer',
        'version' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function humanSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}
