<x-pdf-layout :agency="$agency" :document-title="$documentTitle" :control-no="$controlNo" :verification-url="$verificationUrl">
    <p>Date: {{ optional($ntp->issued_at)->format('F d, Y') ?? now()->format('F d, Y') }}</p>
    <p>
        <strong>{{ $noa?->bidder?->company_name }}</strong><br>
        {{ $noa?->bidder?->address }}
    </p>

    <p class="section-title">Notice to Proceed</p>
    <p>
        Dear Sir/Madam,
    </p>
    <p>
        Please be informed that the {{ $agency->name }} requires you to commence the implementation of
        <strong>{{ $procurement->title }}</strong> (Case No. {{ $procurement->case_no }}) starting
        <strong>{{ optional($ntp->effectivity_date)->format('F d, Y') }}</strong>.
    </p>
    <p>
        You are hereby given <strong>{{ $ntp->contract_duration_days }} calendar days</strong> from the effectivity date to complete
        the contract, or until <strong>{{ optional($ntp->completion_date)->format('F d, Y') }}</strong>, subject to the terms and
        conditions of the contract and the Implementing Rules and Regulations of RA 12009.
    </p>

    <table class="signatories">
        <tr>
            <td style="width: 50%;"><div class="sig-line">{{ $ntp->issuedBy?->name ?? $agency->hope_position }}<br>{{ $agency->hope_position ?? 'Head of Procuring Entity' }}</div></td>
            <td style="width: 50%;"><div class="sig-line">Received By:<br>{{ $noa?->bidder?->company_name }}</div></td>
        </tr>
    </table>
</x-pdf-layout>
