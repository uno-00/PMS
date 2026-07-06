<x-pdf-layout :agency="$agency" :document-title="$documentTitle" :control-no="$controlNo" :verification-url="$verificationUrl">
    <table>
        <tr>
            <td style="width: 50%;"><strong>Supplier:</strong> {{ $po->bidder?->company_name }}</td>
            <td style="width: 50%;"><strong>Mode of Procurement:</strong> {{ $po->modeOfProcurement?->name }}</td>
        </tr>
        <tr>
            <td><strong>Delivery Date:</strong> {{ optional($po->delivery_date)->format('M d, Y') }}</td>
            <td><strong>Delivery Place:</strong> {{ $po->delivery_place }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong> {{ $po->status->label() }}</td>
            <td></td>
        </tr>
    </table>

    <p class="section-title">Items</p>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Unit</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Cost</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->items as $item)
                <tr>
                    <td>{{ $item->item_name }}<br><span class="muted">{{ $item->description }}</span></td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right">{{ rtrim(rtrim($item->quantity, '0'), '.') }}</td>
                    <td class="text-right">&#8369;{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="text-right">&#8369;{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">Subtotal</td>
                <td class="text-right">&#8369;{{ number_format($po->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right">Tax</td>
                <td class="text-right">&#8369;{{ number_format($po->tax_amount, 2) }}</td>
            </tr>
            <tr>
                <td colspan="4" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>&#8369;{{ number_format($po->total_amount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatories">
        <tr>
            <td style="width: 50%;"><div class="sig-line">Prepared By</div></td>
            <td style="width: 50%;"><div class="sig-line">{{ $agency->hope_position ?? 'Head of Procuring Entity' }}</div></td>
        </tr>
    </table>
</x-pdf-layout>
