<x-pdf-layout :agency="$agency" :document-title="$documentTitle" :control-no="$controlNo" :verification-url="$verificationUrl">
    <p>Date: {{ optional($noa->issued_at)->format('F d, Y') ?? now()->format('F d, Y') }}</p>
    <p>
        <strong>{{ $noa->bidder?->company_name }}</strong><br>
        {{ $noa->bidder?->address }}
    </p>
    <p>Attention: {{ $noa->bidder?->contact_person }}</p>

    <p class="section-title">Notice of Award</p>
    <p>
        Dear Sir/Madam,
    </p>
    <p>
        This is to formally notify you that your bid for <strong>{{ $procurement->title }}</strong> (Case No. {{ $procurement->case_no }})
        in the amount of <strong>&#8369;{{ number_format($noa->amount, 2) }}</strong> has been evaluated and is hereby declared as the
        Lowest Calculated and Responsive Bid / Highest Rated and Responsive Bid, and the corresponding contract is hereby awarded to your firm,
        subject to the terms and conditions set forth in the Bidding Documents.
    </p>
    <p>
        Please signify your conformity by signing below and submit the required performance security within the period prescribed under
        the Implementing Rules and Regulations of RA 12009.
    </p>

    <table style="margin-top: 20px;">
        <tr><td style="width: 50%;"><strong>Status:</strong> {{ $noa->status->label() }}</td><td style="width: 50%;"><strong>Amount:</strong> &#8369;{{ number_format($noa->amount, 2) }}</td></tr>
    </table>

    <table class="signatories">
        <tr>
            <td style="width: 50%;"><div class="sig-line">{{ $noa->approvedBy?->name ?? $agency->hope_position }}<br>{{ $agency->hope_position ?? 'Head of Procuring Entity' }}</div></td>
            <td style="width: 50%;"><div class="sig-line">Conforme:<br>{{ $noa->bidder?->company_name }}</div></td>
        </tr>
    </table>
</x-pdf-layout>
