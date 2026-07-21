<div>
    <x-page-header title="My Bids" subtitle="All bid submissions across every procurement opportunity, with full version history." />

    <x-card>
        <x-table.filter-toolbar>Use the column filters below to search bid records.</x-table.filter-toolbar>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-400">
                        <th class="py-2 pr-4">Bid No.</th>
                        <th class="py-2 pr-4">Procurement</th>
                        <th class="py-2 pr-4">Version</th>
                        <th class="py-2 pr-4">Submitted</th>
                        <th class="py-2 pr-4">Status</th>
                        <th class="py-2 pr-4">Evaluation</th>
                        <th class="py-2 pr-4">Date Created</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                    <tr class="border-b border-slate-100 dark:border-slate-800">
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterBidNo" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterProcurement" /></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-text model="filterVersion" placeholder="v…" /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1">
                            <x-table.filter-select model="status">
                                <option value="submitted">Submitted</option>
                                <option value="opened">Opened</option>
                                <option value="disqualified">Disqualified</option>
                                <option value="withdrawn">Withdrawn</option>
                            </x-table.filter-select>
                        </th>
                        <th class="pb-3 pr-4 pt-1"></th>
                        <th class="pb-3 pr-4 pt-1"><x-table.filter-dates /></th>
                        <th class="pb-3 pr-4 pt-1"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($bids as $bid)
                        <tr wire:key="bid-{{ $bid->id }}">
                            <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $bid->bid_no }}</td>
                            <td class="py-3 pr-4">{{ $bid->procurement->title }}</td>
                            <td class="py-3 pr-4">v{{ $bid->version }}</td>
                            <td class="py-3 pr-4 text-xs text-slate-400">{{ $bid->submitted_at?->format('M d, Y g:ia') }}</td>
                            <td class="py-3 pr-4"><x-status-badge :status="new \App\Support\SimpleStatus($bid->status)" /></td>
                            <td class="py-3 pr-4 text-xs">
                                @if($bid->evaluation)
                                    Rank {{ $bid->evaluation->rank ?? '—' }} &middot; {{ ucfirst(str_replace('_',' ', $bid->evaluation->recommendation ?? 'pending')) }}
                                @else
                                    <span class="text-slate-400">Not yet evaluated</span>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-xs text-slate-500">{{ $bid->created_at?->format('M d, Y') }}</td>
                            <td class="py-3 pr-4 text-right"><x-button href="{{ route('bidder.opportunities.show', $bid->procurement) }}" variant="secondary" size="sm">View</x-button></td>
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

        @if($bids->hasPages())
            <div class="mt-4">{{ $bids->links() }}</div>
        @endif
    </x-card>
</div>
