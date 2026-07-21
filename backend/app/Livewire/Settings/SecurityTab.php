<?php

namespace App\Livewire\Settings;

use App\Models\Settings\SystemSetting;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

/**
 * Settings > Security: password policy, session timeout, and audit-log
 * retention. Backed by the generic SystemSetting key/value store (group
 * "security") so these are editable at runtime instead of hardcoded
 * config values — every place that enforces them (PasswordPolicy helper,
 * EnforceSessionTimeout middleware) reads back from the same store.
 */
class SecurityTab extends Component
{
    public int $password_min_length = 12;

    public bool $password_require_mixed_case = true;

    public bool $password_require_numbers = true;

    public bool $password_require_symbols = true;

    public int $password_expiry_days = 90;

    public int $session_timeout_minutes = 30;

    public int $max_login_attempts = 5;

    public int $audit_log_retention_days = 365;

    public function mount(): void
    {
        Gate::authorize('settings.manage');

        $settings = SystemSetting::group('security');

        $this->password_min_length = (int) ($settings['password_min_length'] ?? 12);
        $this->password_require_mixed_case = (bool) ($settings['password_require_mixed_case'] ?? true);
        $this->password_require_numbers = (bool) ($settings['password_require_numbers'] ?? true);
        $this->password_require_symbols = (bool) ($settings['password_require_symbols'] ?? true);
        $this->password_expiry_days = (int) ($settings['password_expiry_days'] ?? 90);
        $this->session_timeout_minutes = (int) ($settings['session_timeout_minutes'] ?? config('session.lifetime'));
        $this->max_login_attempts = (int) ($settings['max_login_attempts'] ?? 5);
        $this->audit_log_retention_days = (int) ($settings['audit_log_retention_days'] ?? 365);
    }

    public function save(): void
    {
        Gate::authorize('settings.manage');

        $this->validate([
            'password_min_length' => 'required|integer|min:6|max:64',
            'password_expiry_days' => 'required|integer|min:0|max:3650',
            'session_timeout_minutes' => 'required|integer|min:1|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'audit_log_retention_days' => 'required|integer|min:30|max:3650',
        ]);

        foreach ([
            'password_min_length', 'password_require_mixed_case', 'password_require_numbers',
            'password_require_symbols', 'password_expiry_days', 'session_timeout_minutes',
            'max_login_attempts', 'audit_log_retention_days',
        ] as $key) {
            SystemSetting::set('security', $key, $this->{$key});
        }

        session()->flash('status', 'Security settings updated.');
    }

    public function render()
    {
        return view('livewire.settings.security-tab');
    }
}
