<div>
    <x-page-header title="Audit Trail" subtitle="Every create/update/delete on an auditable record across all modules, with before/after values." />

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search audit trail entries.</x-table.filter-toolbar>

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
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="logName" placeholder="All modules">
                                @foreach($logNames as $name)
                                    <option value="{{ $name }}">{{ ucfirst(str_replace('_', ' ', $name)) }}</option>
                                @endforeach
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="event" placeholder="All events">
                                <option value="created">Created</option>
                                <option value="updated">Updated</option>
                                <option value="deleted">Deleted</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="search" placeholder="Description…" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterUser" placeholder="User…" /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($activities as $activity)
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
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No records match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($activities->hasPages())
            <div class="mt-4">{{ $activities->links() }}</div>
        @endif
    </x-card>
</div>
