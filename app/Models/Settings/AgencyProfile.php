<?php

namespace App\Models\Settings;

use App\Models\Concerns\HasAuditLog;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AgencyProfile extends Model
{
    use HasAuditLog, HasUuid;

    private static ?self $resolved = null;

    protected $fillable = [
        'name', 'acronym', 'agency_code', 'address', 'region', 'tin',
        'head_of_agency', 'hope_position', 'bac_chairperson', 'logo_path',
        'website', 'contact_email', 'contact_phone', 'philgeps_organization_id',
    ];

    public static function current(): self
    {
        if (static::$resolved) {
            return static::$resolved;
        }

        try {
            if (! Schema::hasTable('agency_profiles')) {
                return static::$resolved = new static(['name' => config('app.name')]);
            }
        } catch (\Throwable) {
            return static::$resolved = new static(['name' => config('app.name')]);
        }

        return static::$resolved = static::query()->first()
            ?? static::query()->create(['name' => config('app.name')]);
    }

    public static function resetCached(): void
    {
        static::$resolved = null;
    }

    public function displayName(): string
    {
        return $this->name ?: config('app.name');
    }

    public function logoUrl(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        // Relative path keeps the current host/port (e.g. :8001 in local dev).
        return '/storage/'.str_replace('\\', '/', $this->logo_path);
    }

    public function initials(): string
    {
        if ($this->acronym) {
            return strtoupper(substr($this->acronym, 0, 3));
        }

        return collect(explode(' ', $this->displayName()))
            ->filter()
            ->map(fn (string $word) => $word[0] ?? '')
            ->take(3)
            ->join('');
    }
}
