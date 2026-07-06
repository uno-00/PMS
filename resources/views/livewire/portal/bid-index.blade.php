<div>
    <x-page-header title="My Bids" subtitle="All bid submissions across every procurement opportunity, with full version history." />

    @if($bids->isEmpty())
        <x-empty-state icon="document-text" title="You haven't submitted any bids yet" />
    @else
        <x-card>
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
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($bids as $bid)
                            <tr>
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
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('bidder.opportunities.show', $bid->procurement) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bids->links() }}</div>
        </x-card>
    @endif
</div>
