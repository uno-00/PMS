<div>
    <x-page-header title="Audit Trail" subtitle="Every create/update/delete on an auditable record across all modules, with before/after values." />

    <x-card class="mb-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-slate-500">Module</label>
                <select wire:model.live="logName" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">All Modules</option>
                    @foreach($logNames as $name)
                        <option value="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">Event</label>
                <select wire:model.live="event" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
                    <option value="">All Events</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">From</label>
                <input wire:model.live="dateFrom" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">To</label>
                <input wire:model.live="dateTo" type="date" class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">Search</label>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Description..." class="mt-1 block w-full rounded-lg border-slate-300 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            </div>
        </div>
        <div class="mt-3 text-right">
            <x-button size="sm" variant="ghost" wire:click="resetFilters">Clear Filters</x-button>
        </div>
    </x-card>

    @if($activities->isEmpty())
        <x-empty-state icon="shield-check" title="No audit entries match your filters" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Date/Time</th>
                            <th class="py-2 pr-4">Module</th>
                            <th class="py-2 pr-4">Event</th>
                            <th class="py-2 pr-4">Description</th>
                            <th class="py-2 pr-4">User</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($activities as $activity)
                            <tr wire:key="activity-{{ $activity->id }}">
                                <td class="py-2.5 pr-4 text-xs text-slate-500">{{ $activity->created_at->format('M d, Y g:ia') }}</td>
                                <td class="py-2.5 pr-4"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs dark:bg-slate-800">{{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}</span></td>
                                <td class="py-2.5 pr-4">
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' => $activity->event === 'created',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $activity->event === 'updated',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $activity->event === 'deleted',
                                    ])>{{ ucfirst($activity->event ?? '—') }}</span>
                                </td>
                                <td class="py-2.5 pr-4">{{ $activity->description }}</td>
                                <td class="py-2.5 pr-4">{{ $activity->causer?->name ?? 'System' }}</td>
                                <td class="py-2.5 pr-4 text-right">
                                    @if(($activity->properties['attributes'] ?? null) || ($activity->properties['old'] ?? null))
                                        <x-button size="sm" variant="ghost" wire:click="toggleDetails('{{ $activity->id }}')">{{ $activeActivityId === $activity->id ? 'Hide' : 'View' }}</x-button>
                                    @endif
                                </td>
                            </tr>
                            @if($activeActivityId === $activity->id)
                                <tr>
                                    <td colspan="6" class="bg-slate-50 px-4 py-3 dark:bg-slate-800/50">
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                            <div>
                                                <p class="text-xs font-semibold uppercase text-slate-400">Before</p>
                                                <pre class="mt-1 overflow-x-auto rounded bg-white p-2 text-xs dark:bg-slate-900">{{ json_encode($activity->properties['old'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                            <div>
                                                <p class="text-xs font-semibold uppercase text-slate-400">After</p>
                                                <pre class="mt-1 overflow-x-auto rounded bg-white p-2 text-xs dark:bg-slate-900">{{ json_encode($activity->properties['attributes'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $activities->links() }}</div>
        </x-card>
    @endif
</div>
