<div>
    <x-page-header title="BAC Members &amp; TWG" subtitle="Official BAC roster with Primary and Alternate TWG personnel per procurement category." />

    @if (session('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">{{ session('status') }}</div>
    @endif

    <div class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-3">
        @foreach($twgCategories as $category)
            @php $slots = $twgRoster[$category->value] ?? ['primary' => null, 'alternate' => null]; @endphp
            <div class="rounded-lg border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">TWG &mdash; {{ $category->label() }}</p>
                <div class="mt-3 space-y-2 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase text-primary-700 dark:text-primary-300">Primary</p>
                        @if($slots['primary'])
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $slots['primary']->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $slots['primary']->user->email }}</p>
                        @else
                            <p class="text-slate-400">Not assigned</p>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase text-slate-500">Alternate</p>
                        @if($slots['alternate'])
                            <p class="font-medium text-slate-800 dark:text-slate-100">{{ $slots['alternate']->user->name }}</p>
                            <p class="text-xs text-slate-500">{{ $slots['alternate']->user->email }}</p>
                        @else
                            <p class="text-slate-400">Not assigned</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <input wire:model.live.debounce.400ms="search" type="search" placeholder="Search name or email..."
                   class="min-w-[14rem] rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <select wire:model.live="roleFilter" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">All BAC roles</option>
                @foreach($bacRoles as $role)
                    <option value="{{ $role->value }}">{{ $role->label() }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryFilter" class="rounded-lg border-slate-300 text-sm shadow-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                <option value="">All TWG categories</option>
                @foreach($twgCategories as $category)
                    <option value="{{ $category->value }}">{{ $category->label() }}</option>
                @endforeach
            </select>
        </div>
        @can('bac-members.create')
            <x-button size="sm" wire:click="openCreate">Add BAC member</x-button>
        @endcan
    </div>

    @if($members->isEmpty())
        <x-empty-state icon="users" title="No BAC members on the roster yet" description="Add chairperson, secretariat, and members with Primary or Alternate TWG assignments." />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Member</th>
                            <th class="py-2 pr-4">BAC Role</th>
                            <th class="py-2 pr-4">TWG Assignments</th>
                            <th class="py-2 pr-4">Designation</th>
                            <th class="py-2 pr-4">Term</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($members as $member)
                            <tr wire:key="bac-member-{{ $member->id }}">
                                <td class="py-3 pr-4">
                                    <p class="font-medium text-slate-800 dark:text-slate-100">{{ $member->user->name }}</p>
                                    <p class="text-xs text-slate-500">{{ $member->user->email }}</p>
                                </td>
                                <td class="py-3 pr-4">{{ $member->bac_role->label() }}</td>
                                <td class="py-3 pr-4">
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($member->twgAssignmentsSummary() as $assignment)
                                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $assignment['designation'] === \App\Enums\TwGDesignationType::Primary
                                                    ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300'
                                                    : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                {{ $assignment['category']->label() }} ({{ $assignment['designation']->label() }})
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-400">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="py-3 pr-4 text-slate-600 dark:text-slate-300">{{ $member->designation ?? '—' }}</td>
                                <td class="py-3 pr-4 text-xs text-slate-500">
                                    @if($member->term_start || $member->term_end)
                                        {{ $member->term_start?->format('M j, Y') ?? '—' }} &mdash; {{ $member->term_end?->format('M j, Y') ?? 'Present' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if($member->is_active)
                                        <span class="text-emerald-600">Active</span>
                                    @else
                                        <span class="text-slate-400">Inactive</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4 text-right">
                                    @can('bac-members.edit')
                                        <div class="flex justify-end gap-2">
                                            <button type="button" wire:click="openEdit('{{ $member->id }}')" class="text-xs font-medium text-primary-700 hover:underline dark:text-primary-400">Edit</button>
                                            <button type="button" wire:click="toggleActive('{{ $member->id }}')" class="text-xs font-medium text-slate-500 hover:underline">
                                                {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </div>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $members->links() }}</div>
        </x-card>
    @endif

    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl dark:bg-slate-900">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ $editingId ? 'Edit BAC member' : 'Add BAC member' }}
                </h3>
                <p class="mt-1 text-sm text-slate-500">Assign BAC role and TWG slots (Primary or Alternate) per category.</p>

                <form wire:submit="save" class="mt-5 space-y-4">
                    @if(!$editingId)
                        <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                            <input wire:model.live="createNewUser" type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                            Create a new user account
                        </label>
                    @endif

                    @if($createNewUser && !$editingId)
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
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Temporary password</label>
                            <input wire:model="password" type="password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">User account</label>
                            <select wire:model="user_id" @disabled((bool) $editingId) class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                <option value="">Select user</option>
                                @if($editingId && $user_id)
                                    <option value="{{ $user_id }}">{{ $name }} ({{ $email }})</option>
                                @endif
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                            @error('user_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">BAC role</label>
                        <select wire:model="bac_role" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @foreach($bacRoles as $role)
                                <option value="{{ $role->value }}">{{ $role->label() }}</option>
                            @endforeach
                        </select>
                        @error('bac_role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Designation <span class="font-normal text-slate-400">(optional)</span></label>
                        <input wire:model="designation" type="text" placeholder="e.g. Civil Engineer — TWG Infrastructure" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>

                    <div>
                        <p class="text-sm font-medium text-slate-700 dark:text-slate-300">TWG assignments</p>
                        <p class="mt-0.5 text-xs text-slate-500">Each category allows one Primary and one Alternate personnel system-wide.</p>
                        <div class="mt-2 space-y-2">
                            @foreach($twgCategories as $category)
                                <div class="rounded-lg border border-slate-200 px-3 py-2 dark:border-slate-700">
                                    <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ $category->label() }}</p>
                                    <div class="mt-2 flex flex-wrap gap-3 text-sm text-slate-600 dark:text-slate-300">
                                        <label class="flex items-center gap-1.5">
                                            <input type="radio" wire:model="twg_assignments.{{ $category->value }}" value="" class="border-slate-300 text-primary-600 focus:ring-primary-500">
                                            None
                                        </label>
                                        @foreach($twgDesignationTypes as $type)
                                            <label class="flex items-center gap-1.5">
                                                <input type="radio" wire:model="twg_assignments.{{ $category->value }}" value="{{ $type->value }}" class="border-slate-300 text-primary-600 focus:ring-primary-500">
                                                {{ $type->label() }}
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Term start</label>
                            <input wire:model="term_start" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('term_start') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Term end</label>
                            <input wire:model="term_end" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                            @error('term_end') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                        <input wire:model="is_active" type="checkbox" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        Active on roster
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <x-button type="button" variant="secondary" wire:click="$set('showModal', false)">Cancel</x-button>
                        <x-button type="submit">Save member</x-button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
