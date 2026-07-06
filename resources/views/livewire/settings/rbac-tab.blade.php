<div>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <p class="text-sm text-slate-600 dark:text-slate-400">
        Assign permissions to each role. Super Admin always has full access and cannot be restricted.
    </p>

    <div class="mt-4 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-1 lg:col-span-1">
            @foreach($roles as $role)
                <button type="button" wire:click="selectRole('{{ $role->name }}')"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition
                            {{ $selectedRole === $role->name ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    <span class="font-medium">{{ $role->name }}</span>
                    <span class="text-xs text-slate-400">{{ $role->permissions_count }} perms</span>
                </button>
            @endforeach
        </div>

        <div class="lg:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">{{ $selectedRole }}</h3>
                @if($selectedRole !== \App\Support\Roles::SUPER_ADMIN)
                    <x-button size="sm" wire:click="save">Save permissions</x-button>
                @else
                    <span class="text-xs text-slate-400">Full access (read-only)</span>
                @endif
            </div>

            @if($selectedRole === \App\Support\Roles::SUPER_ADMIN)
                <p class="mt-3 rounded-lg border border-dashed border-slate-300 px-4 py-3 text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                    Super Admin bypasses all policy checks via <code class="text-xs">Gate::before</code> and holds every permission.
                </p>
            @endif

            <div class="mt-4 max-h-[32rem] space-y-4 overflow-y-auto pr-1">
                @foreach($matrix as $module => $actions)
                    @php
                        $modulePermissions = array_map(fn ($action) => "{$module}.{$action}", $actions);
                        $selectedCount = count(array_intersect($modulePermissions, $selectedPermissions));
                    @endphp
                    <div class="rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ str_replace('-', ' ', $module) }}</h4>
                            @if($selectedRole !== \App\Support\Roles::SUPER_ADMIN)
                                <button type="button" wire:click="toggleModule('{{ $module }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">
                                    {{ $selectedCount === count($modulePermissions) ? 'Clear all' : 'Select all' }}
                                </button>
                            @endif
                        </div>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                            @foreach($actions as $action)
                                @php $permission = "{$module}.{$action}"; @endphp
                                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                    <input type="checkbox"
                                           @checked(in_array($permission, $selectedPermissions, true) || $selectedRole === \App\Support\Roles::SUPER_ADMIN)
                                           @disabled($selectedRole === \App\Support\Roles::SUPER_ADMIN)
                                           wire:click="togglePermission('{{ $permission }}')"
                                           class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                    <span>{{ str_replace('-', ' ', $action) }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
