<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use App\Models\Settings\Division;
use App\Models\Settings\Office;
use App\Models\Supplier\Bidder;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuid, LogsActivity, Notifiable, SoftDeletes;

    protected $guard_name = 'web';

    protected $fillable = [
        'employee_number',
        'name',
        'email',
        'password',
        'position',
        'phone',
        'avatar_path',
        'signature_path',
        'division_id',
        'office_id',
        'is_active',
        'must_change_password',
        'password_changed_at',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'password_changed_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function bidder(): HasOne
    {
        return $this->hasOne(Bidder::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'position', 'is_active', 'division_id', 'office_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function bacMember(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Bac\BacMember::class);
    }

    public function primaryRoleName(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function dashboardRoute(): string
    {
        $key = collect(config('rbac.dashboards'))
            ->get($this->primaryRoleName(), 'division');

        // Dotted values (e.g. "bidder.dashboard" for the separate Bidder
        // Portal) are already full route names; everything else maps into
        // the internal app's dashboard.{type} route.
        return str_contains($key, '.') ? $key : "dashboard.{$key}";
    }
}
