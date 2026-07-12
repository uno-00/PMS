<?php

namespace App\Support;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Sidebar visibility checks. Unlike route permissions, nav links must be
 * hidden for everyone (including Super Admin) when a module is deactivated
 * in Settings > Module Management.
 */
final class NavAccess
{
    public static function can(string $permission, ?Authenticatable $user = null, ?array $moduleStates = null): bool
    {
        $user ??= Auth::user();

        if ($user === null) {
            return false;
        }

        if (! self::moduleIsVisibleInNav($permission, $moduleStates)) {
            return false;
        }

        return $user->can($permission);
    }

    /** @param  array<int, string>|string  $permissions */
    public static function canAny(array|string $permissions, ?Authenticatable $user = null, ?array $moduleStates = null): bool
    {
        foreach ((array) $permissions as $permission) {
            if (self::can($permission, $user, $moduleStates)) {
                return true;
            }
        }

        return false;
    }

    /** @param  array<string, bool>|null  $moduleStates */
    protected static function moduleIsVisibleInNav(string $permission, ?array $moduleStates = null): bool
    {
        $module = Str::contains($permission, '.')
            ? Str::before($permission, '.')
            : $permission;

        if (! ModuleRegistry::isToggleable($module)) {
            return true;
        }

        if ($moduleStates !== null) {
            return $moduleStates[$module] ?? true;
        }

        try {
            return ModuleState::isActive($module);
        } catch (\Throwable) {
            return true;
        }
    }
}
