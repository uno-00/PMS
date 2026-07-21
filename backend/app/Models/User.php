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
    use HasApiTokens, HasFactory, HasRoles {
        HasRoles::hasPermissionTo as spatieHasPermissionTo;
    }
    use HasUuid, LogsActivity, Notifiable, SoftDeletes;

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

    /**
     * Module activation layer. Spatie's Gate::before resolves permissions
     * through hasPermissionTo before any application Gate callback can run,
     * so this is the single point where a deactivated module's permissions
     * are denied for everyone except Super Admin. When a module is toggled
     * off in Settings > Module Management, every {module}.{action} ability
     * it defines resolves to false here, which also hides its nav link
     * (nav is @can-gated) and denies its routes/policies. Super Admin is
     * always exempt so an administrator can never lock themselves out.
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        $granted = $this->spatieHasPermissionTo($permission, $guardName);

        if (! $granted || $this->hasRole(\App\Support\Roles::SUPER_ADMIN)) {
            return $granted;
        }

        $key = $permission instanceof \Spatie\Permission\Contracts\Permission
            ? $permission->name
            : (string) $permission;

        $module = \Illuminate\Support\Str::before($key, '.');

        if (! \App\Support\ModuleRegistry::isToggleable($module)) {
            return $granted;
        }

        try {
            return \App\Support\ModuleState::isActive($module);
        } catch (\Throwable) {
            return $granted;
        }
    }
}
