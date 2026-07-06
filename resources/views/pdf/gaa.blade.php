<x-pdf-layout :agency="$agency" :document-title="$documentTitle" :control-no="$controlNo" :verification-url="$verificationUrl">
    <table>
        <tr>
            <td style="width: 50%;"><strong>Fiscal Year:</strong> {{ $gaa->fiscalYear->year }}</td>
            <td style="width: 50%;"><strong>Status:</strong> {{ $gaa->status->label() }}</td>
        </tr>
        <tr>
            <td><strong>Reference No.:</strong> {{ $gaa->reference_no }}</td>
            <td><strong>Total Amount:</strong> &#8369;{{ number_format($gaa->total_amount, 2) }}</td>
        </tr>
    </table>

    <p class="section-title">Budget Summary by Department</p>
    <table class="items-table">
        <thead>
            <tr>
                <th>Department</th>
                <th class="text-right">Amount</th>
                <th class="text-right">% of Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($summary as $row)
                <tr>
                    <td>{{ $row->department?->name ?? '—' }}</td>
                    <td class="text-right">&#8369;{{ number_format($row->total, 2) }}</td>
                    <td class="text-right">{{ $gaa->total_amount > 0 ? number_format(($row->total / $gaa->total_amount) * 100, 1) : 0 }}%</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>&#8369;{{ number_format($gaa->total_amount, 2) }}</strong></td>
                <td class="text-right"><strong>100%</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatories">
        <tr>
            <td style="width: 50%;"><div class="sig-line">{{ $gaa->uploader?->name ?? 'Budget Officer' }}</div></td>
            <td style="width: 50%;"><div class="sig-line">{{ $agency->hope_position ?? 'Head of Procuring Entity' }}</div></td>
        </tr>
    </table>
</x-pdf-layout>
