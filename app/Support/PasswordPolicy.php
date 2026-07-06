<?php

namespace App\Support;

use App\Models\Settings\SystemSetting;
use Illuminate\Validation\Rules\Password;

/**
 * Reads the configurable "Password Policy" (Settings > Security) so every
 * place that validates a new password (force-change, bidder registration,
 * user management) enforces the same, admin-editable rule instead of a
 * hardcoded Password::min(12).
 */
final class PasswordPolicy
{
    public static function rule(): Password
    {
        $min = (int) SystemSetting::get('security', 'password_min_length', 12);

        $rule = Password::min(max($min, 6));

        if ((bool) SystemSetting::get('security', 'password_require_mixed_case', true)) {
            $rule = $rule->mixedCase();
        }

        if ((bool) SystemSetting::get('security', 'password_require_numbers', true)) {
            $rule = $rule->numbers();
        }

        if ((bool) SystemSetting::get('security', 'password_require_symbols', true)) {
            $rule = $rule->symbols();
        }

        return $rule;
    }

    public static function sessionTimeoutMinutes(): int
    {
        return (int) SystemSetting::get('security', 'session_timeout_minutes', config('session.lifetime'));
    }
}
