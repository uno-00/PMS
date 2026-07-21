<div>
    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-600 dark:text-slate-400">Manage agency user accounts, roles, and activation status.</p>
        @can('users.create')
            <x-button size="sm" wire:click="openCreate">Add user</x-button>
        @endcan
    </div>

    <x-card class="mt-4">
        <x-table.filter-toolbar>Use the column filters below to search user records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Name</th>
                        <th class="py-2 pr-4">Email</th>
                        <th class="py-2 pr-4">Role</th>
                        <th class="py-2 pr-4">Division</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Last login</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterName" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterEmail" /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="roleFilter" placeholder="All roles">
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterDivisionId" placeholder="All divisions">
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="filterStatus" placeholder="All">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($users as $user)
                        <tr wire:key="user-{{ $user->id }}">
                            <td class="py-2.5 pr-4 font-medium text-slate-800 dark:text-slate-100">{{ $user->name }}</td>
                            <td class="py-2.5 pr-4 text-slate-600 dark:text-slate-300">{{ $user->email }}</td>
                            <td class="py-2.5 pr-4">{{ $user->primaryRoleName() ?? '—' }}</td>
                            <td class="py-2.5 pr-4">{{ $user->division?->name ?? '—' }}</td>
                            <td class="py-2.5 pr-4">
                                @if($user->is_active)
                                    <span class="text-emerald-600">Active</span>
                                @else
                                    <span class="text-slate-400">Inactive</span>
                                @endif
                            </td>
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $user->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $user->created_at?->format('M d, Y') }}</td>
                            <td class="py-2.5 pr-4 text-right">
                                <div class="flex justify-end gap-2">
                                    @can('users.edit')
                                        <button type="button" wire:click="openEdit('{{ $user->id }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">Edit</button>
                                        <button type="button" wire:click="toggleActive('{{ $user->id }}')" class="text-xs font-medium text-slate-500 hover:underline">
                                            {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    @endcan
                                    @can('users.reset-password')
                                        <button type="button" wire:click="openResetPassword('{{ $user->id }}')" class="text-xs font-medium text-amber-700 hover:underline dark:text-amber-400">Reset password</button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="mt-4">{{ $users->links() }}</div>
        @endif
    </x-card>

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $editingUserId ? 'Edit user' : 'Add user' }}</h3>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Full name</label>
                        <input wire:model="name" type="text" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                        <input wire:model="email" type="email" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                            Password {{ $editingUserId ? '(leave blank to keep current)' : '' }}
                        </label>
                        <div class="mt-1 flex gap-2">
                            <input wire:model="password" type="password" class="block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @can('users.reset-password')
                                <x-button type="button" size="sm" variant="secondary" wire:click="generateResetPassword">Generate</x-button>
                            @endcan
                        </div>
                        @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Role</label>
                            <select wire:model="role_name" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white" @disabled(! auth()->user()->can('users.manage-roles'))>
                                <option value="">Select role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ $role }}</option>
                                @endforeach
                            </select>
                            @error('role_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Division</label>
                            <select wire:model="division_id" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">None</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        Account is active
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Cancel</x-button>
                        <x-button type="submit">Save user</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if($showResetModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4" wire:click.self="closeResetPasswordModal">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Reset password</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ $resetUserName }} &middot; {{ $resetUserEmail }}
                </p>

                @if($resetPasswordResult)
                    <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-800 dark:bg-emerald-900/20" x-data="{ copied: false }">
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">Password updated successfully</p>
                        <p class="mt-2 break-all font-mono text-sm text-slate-800 dark:text-slate-100">{{ $resetPasswordResult }}</p>
                        <p class="mt-2 text-xs text-slate-500">Share this password securely. The user must change it on next login. Active sessions were signed out.</p>
                        <div class="mt-3 flex gap-2">
                            <x-button type="button" size="sm" variant="secondary"
                                      @click="navigator.clipboard.writeText(@js($resetPasswordResult)); copied = true">
                                <span x-show="!copied">Copy password</span>
                                <span x-show="copied" x-cloak>Copied!</span>
                            </x-button>
                            <x-button type="button" size="sm" wire:click="closeResetPasswordModal">Done</x-button>
                        </div>
                    </div>
                @else
                    <form wire:submit="applyResetPassword" class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">New password</label>
                            <div class="mt-1 flex gap-2">
                                <input wire:model="resetPassword" type="text" autocomplete="new-password"
                                       class="block w-full rounded-lg border-slate-300 font-mono text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <x-button type="button" size="sm" variant="secondary" wire:click="generateResetPassword">Generate</x-button>
                            </div>
                            @error('resetPassword') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Confirm password</label>
                            <input wire:model="resetPasswordConfirmation" type="text" autocomplete="new-password"
                                   class="mt-1 block w-full rounded-lg border-slate-300 font-mono text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('resetPasswordConfirmation') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="text-xs text-slate-500">The user will be required to change this password on next login. Any active sessions for this account will be ended.</p>
                        <div class="flex justify-end gap-2 pt-2">
                            <x-button type="button" variant="secondary" wire:click="closeResetPasswordModal">Cancel</x-button>
                            <x-button type="submit">Reset password</x-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    @endif
</div>
