<div>
    <x-page-header title="Certificate of Availability of Funds" subtitle="Automatically generated once a Purchase Request clears HOPE approval." />

    @if($cafs->isEmpty())
        <x-empty-state icon="check-badge" title="No CAFs generated yet" />
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">CAF No.</th>
                            <th class="py-2 pr-4">Purchase Request</th>
                            <th class="py-2 pr-4">Fund Source</th>
                            <th class="py-2 pr-4 text-right">Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($cafs as $caf)
                            <tr>
                                <td class="py-3 pr-4 font-mono text-xs text-slate-500">{{ $caf->caf_no }}</td>
                                <td class="py-3 pr-4">{{ $caf->purchaseRequest?->pr_no }} &middot; {{ $caf->purchaseRequest?->division?->name }}</td>
                                <td class="py-3 pr-4">{{ $caf->fundSource?->name }}</td>
                                <td class="py-3 pr-4 text-right">₱{{ number_format($caf->amount, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$caf->status" /></td>
                                <td class="py-3 pr-4 text-right"><x-button href="{{ route('cafs.show', $caf) }}" variant="secondary" size="sm">View</x-button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $cafs->links() }}</div>
        </x-card>
    @endif
</div>
