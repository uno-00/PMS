<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Standardizes Spatie Activitylog configuration across every auditable
 * model in the system: log only changed (dirty) attributes, skip empty
 * change sets, and tag entries with the model's own log name so the
 * system-wide Audit Trail report can filter by module.
 */
trait HasAuditLog
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->auditableAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->auditLogName());
    }

    /**
     * Override in the model to restrict which attributes are audited.
     * Defaults to every fillable attribute.
     */
    protected function auditableAttributes(): array
    {
        return $this->fillable ?: ['*'];
    }

    protected function auditLogName(): string
    {
        return Str::snake(class_basename($this));
    }
}
