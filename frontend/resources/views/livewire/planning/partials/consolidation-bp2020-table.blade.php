<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
        <thead>
            <tr class="text-left text-xs uppercase text-slate-400">
                <th class="py-2 pr-4">Program</th>
                <th class="py-2 pr-4">Activity</th>
                <th class="py-2 pr-4">Project / Item</th>
                <th class="py-2 pr-4">Qty</th>
                <th class="py-2 pr-4">Unit Cost</th>
                <th class="py-2 pr-4">Annual Req.</th>
                <th class="py-2 pr-4">Allocation</th>
                <th class="py-2 pr-4">Fund Source</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($lines as $line)
                <tr>
                    <td class="py-2.5 pr-4">{{ $line->program ?? '—' }}</td>
                    <td class="py-2.5 pr-4">{{ $line->activity ?? '—' }}</td>
                    <td class="py-2.5 pr-4 font-medium">{{ $line->procurement_item }}</td>
                    <td class="py-2.5 pr-4">{{ rtrim(rtrim($line->quantity, '0'), '.') }} {{ $line->unit }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->unit_cost, 2) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->annual_requirement, 2) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->budget_allocation, 2) }}</td>
                    <td class="py-2.5 pr-4">{{ $line->fund_source ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="py-8 text-center text-slate-500">No BP Form 2020 lines yet.</td></tr>
            @endforelse
        </tbody>
        @if($lines->isNotEmpty())
            <tfoot>
                <tr class="font-bold">
                    <td colspan="6" class="py-2.5 pr-4 text-right">TOTAL</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($lines->sum('budget_allocation'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
