<?php

namespace App\Support;

use App\Models\Settings\SystemSetting;

/**
 * Tiny read-layer over the SystemSetting "modules" group so the
 * hasPermissionTo override and any UI layer share one cached accessor for
 * whether a module is active. Defaults to active (true) when no stored
 * value exists, so the system behaves as fully-featured out of the box.
 */
final class ModuleState
{
    public static function isActive(string $module): bool
    {
        return filter_var(SystemSetting::get('modules', $module, '1'), FILTER_VALIDATE_BOOLEAN);
    }

    public static function set(string $module, bool $active): void
    {
        SystemSetting::set('modules', $module, $active ? '1' : '0');
    }
}
