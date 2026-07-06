<div>
    <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Security</h3>
    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Password policy, session timeout, and audit trail retention. Changes apply immediately to new logins and password changes.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <form wire:submit="save" class="mt-4 space-y-6">
        <div>
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Password Policy</h4>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Minimum Length</label>
                    <input wire:model="password_min_length" type="number" min="6" max="64" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @error('password_min_length') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Expiry (days, 0 = never)</label>
                    <input wire:model="password_expiry_days" type="number" min="0" max="3650" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input wire:model="password_require_mixed_case" type="checkbox" class="rounded border-slate-300 text-primary-600"> Require upper &amp; lowercase letters
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input wire:model="password_require_numbers" type="checkbox" class="rounded border-slate-300 text-primary-600"> Require numbers
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                    <input wire:model="password_require_symbols" type="checkbox" class="rounded border-slate-300 text-primary-600"> Require symbols
                </label>
            </div>
        </div>

        <hr class="border-slate-200 dark:border-slate-800">

        <div>
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Session &amp; Login</h4>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Session Timeout (minutes)</label>
                    <input wire:model="session_timeout_minutes" type="number" min="1" max="1440" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @error('session_timeout_minutes') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Max Failed Login Attempts</label>
                    <input wire:model="max_login_attempts" type="number" min="1" max="20" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                </div>
            </div>
        </div>

        <hr class="border-slate-200 dark:border-slate-800">

        <div>
            <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-200">Audit Trail</h4>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Retention Period (days)</label>
                    <input wire:model="audit_log_retention_days" type="number" min="30" max="3650" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    @error('audit_log_retention_days') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">Audit log entries older than this are pruned nightly.</p>
                </div>
            </div>
        </div>

        <x-button type="submit" wire:loading.attr="disabled" wire:target="save">Save Security Settings</x-button>
    </form>
</div>
