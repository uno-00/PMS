<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>WFP — {{ $consolidation->reference_no }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #111827; padding: 4px; vertical-align: top; }
        th { background: #f8fafc; font-weight: bold; }
        .header { text-align: center; margin-bottom: 10px; }
        .title { font-size: 12px; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $agency->displayName() }}</div>
        <div class="title">Work and Financial Plan (WFP)</div>
        <div>FY {{ $consolidation->fiscalYear?->year }} · {{ $consolidation->reference_no }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Activity</th>
                <th>Responsible Office</th>
                <th>Expected Output</th>
                <th>Q1</th>
                <th>Q2</th>
                <th>Q3</th>
                <th>Q4</th>
                <th>Total</th>
                <th>Schedule</th>
                <th>Funding</th>
            </tr>
        </thead>
        <tbody>
            @foreach($consolidation->wfpLines as $line)
                <tr>
                    <td>{{ $line->activity }}</td>
                    <td>{{ $line->responsible_office ?? '—' }}</td>
                    <td>{{ $line->expected_output ?? '—' }}</td>
                    <td class="text-right">₱{{ number_format($line->q1_budget, 2) }}</td>
                    <td class="text-right">₱{{ number_format($line->q2_budget, 2) }}</td>
                    <td class="text-right">₱{{ number_format($line->q3_budget, 2) }}</td>
                    <td class="text-right">₱{{ number_format($line->q4_budget, 2) }}</td>
                    <td class="text-right">₱{{ number_format($line->budget_allocation, 2) }}</td>
                    <td>{{ $line->schedule_start?->format('m/Y') ?? '—' }} – {{ $line->schedule_end?->format('m/Y') ?? '—' }}</td>
                    <td>{{ $line->funding_source ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-right"><strong>₱{{ number_format($consolidation->wfpLines->sum('budget_allocation'), 2) }}</strong></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
