<div>
    <x-page-header title="General Appropriations Act" subtitle="DBM-issued budgets per fiscal year, from upload through distribution.">
        <x-slot:actions>
            @can('upload', \App\Models\Budget\GeneralAppropriationsAct::class)
                <x-button href="{{ route('gaa.create') }}">
                    <x-icon name="plus" class="h-4 w-4" /> Upload GAA
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    @if($gaas->isEmpty())
        <x-empty-state icon="banknotes" title="No GAA records yet" description="Upload the DBM Excel template for a fiscal year to get started.">
            @can('upload', \App\Models\Budget\GeneralAppropriationsAct::class)
                <x-slot:actions>
                    <x-button href="{{ route('gaa.create') }}">Upload GAA</x-button>
                </x-slot:actions>
            @endcan
        </x-empty-state>
    @else
        <x-card>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
                    <thead>
                        <tr class="text-left text-xs uppercase text-slate-400">
                            <th class="py-2 pr-4">Fiscal Year</th>
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">Reference No.</th>
                            <th class="py-2 pr-4">Total Amount</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($gaas as $gaa)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-700 dark:text-slate-200">{{ $gaa->fiscalYear->year }}</td>
                                <td class="py-3 pr-4">{{ $gaa->title }}</td>
                                <td class="py-3 pr-4 text-slate-500">{{ $gaa->reference_no ?? '—' }}</td>
                                <td class="py-3 pr-4">₱{{ number_format($gaa->total_amount, 2) }}</td>
                                <td class="py-3 pr-4"><x-status-badge :status="$gaa->status" /></td>
                                <td class="py-3 pr-4 text-right">
                                    <x-button href="{{ route('gaa.show', $gaa) }}" variant="secondary" size="sm">View</x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $gaas->links() }}</div>
        </x-card>
    @endif
</div>
