<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Configures an Eloquent model to use a UUID (v4) as its primary key
 * instead of the default auto-incrementing integer.
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function initializeHasUuid(): void
    {
        $this->keyType = 'string';
        $this->incrementing = false;
    }

    public function getIncrementing(): bool
    {
        return false;
    }

    public function getKeyType(): string
    {
        return 'string';
    }
}
