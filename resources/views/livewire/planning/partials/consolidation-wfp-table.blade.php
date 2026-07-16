<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-800">
        <thead>
            <tr class="text-left text-xs uppercase text-slate-400">
                <th class="py-2 pr-4">Activity</th>
                <th class="py-2 pr-4">Office</th>
                <th class="py-2 pr-4">Output</th>
                <th class="py-2 pr-4">Q1</th>
                <th class="py-2 pr-4">Q2</th>
                <th class="py-2 pr-4">Q3</th>
                <th class="py-2 pr-4">Q4</th>
                <th class="py-2 pr-4">Total</th>
                <th class="py-2 pr-4">Schedule</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($lines as $line)
                <tr>
                    <td class="py-2.5 pr-4 font-medium">{{ $line->activity }}</td>
                    <td class="py-2.5 pr-4">{{ $line->responsible_office ?? '—' }}</td>
                    <td class="py-2.5 pr-4 text-xs">{{ Str::limit($line->expected_output, 60) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->q1_budget, 2) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->q2_budget, 2) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->q3_budget, 2) }}</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($line->q4_budget, 2) }}</td>
                    <td class="py-2.5 pr-4 font-medium">₱{{ number_format($line->budget_allocation, 2) }}</td>
                    <td class="py-2.5 pr-4 text-xs text-slate-500">
                        {{ $line->schedule_start?->format('m/Y') ?? '—' }} – {{ $line->schedule_end?->format('m/Y') ?? '—' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="py-8 text-center text-slate-500">No WFP lines yet.</td></tr>
            @endforelse
        </tbody>
        @if($lines->isNotEmpty())
            <tfoot>
                <tr class="font-bold">
                    <td colspan="7" class="py-2.5 pr-4 text-right">TOTAL</td>
                    <td class="py-2.5 pr-4">₱{{ number_format($lines->sum('budget_allocation'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>
</div>
