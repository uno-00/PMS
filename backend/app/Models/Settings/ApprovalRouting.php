<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ApprovalRouting extends Model
{
    use HasAuditLog, HasUuid;

    protected $fillable = ['document_type', 'step_key', 'step_label', 'sequence', 'role_name', 'is_active'];

    protected $casts = ['is_active' => 'boolean', 'sequence' => 'integer'];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('approval_routings'));
        static::deleted(fn () => Cache::forget('approval_routings'));
    }

    /**
     * The role currently responsible for a given step of a document's
     * workflow. Services call this instead of hardcoding a role name so
     * a Super Admin can reassign approval authority from Settings without
     * a deployment.
     */
    public static function roleFor(string $documentType, string $stepKey): ?string
    {
        return static::query()
            ->where('document_type', $documentType)
            ->where('step_key', $stepKey)
            ->where('is_active', true)
            ->value('role_name');
    }

    public static function stepsFor(string $documentType)
    {
        return static::query()
            ->where('document_type', $documentType)
            ->where('is_active', true)
            ->orderBy('sequence')
            ->get();
    }
}
