<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Market Scoping Checklist — {{ $record->control_no }}</title>
    <style>
        @page { margin: 28px 24px 36px 24px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111827; line-height: 1.35; }
        table { border-collapse: collapse; width: 100%; }
        .letterhead { text-align: center; margin-bottom: 10px; }
        .letterhead-logo { height: 48px; max-width: 72px; object-fit: contain; margin-bottom: 4px; }
        .agency-name { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .agency-meta { font-size: 8px; color: #475569; margin-top: 2px; }
        .form-title { text-align: center; font-size: 12px; font-weight: bold; text-transform: uppercase; margin: 8px 0 10px; }
        .section-title { font-size: 10px; font-weight: bold; margin: 12px 0 6px; text-transform: uppercase; }
        .section-note { font-size: 8px; color: #475569; margin-bottom: 6px; }
        .meta-table td { border: 1px solid #111827; padding: 5px 6px; vertical-align: top; }
        .meta-label { font-weight: bold; width: 34%; background: #f8fafc; }
        .data-table th, .data-table td { border: 1px solid #111827; padding: 4px 5px; vertical-align: top; font-size: 8px; }
        .data-table th { background: #f8fafc; font-weight: bold; text-align: center; }
        .check-col { width: 6%; text-align: center; font-weight: bold; }
        .doc-col { width: 34%; }
        .param-col { width: 34%; }
        .considered-col { width: 12%; text-align: center; }
        .recommend-col { width: 34%; }
        .pre-wrap { white-space: pre-wrap; }
        .notes { margin-top: 8px; font-size: 7.5px; color: #475569; }
        .signatures { margin-top: 18px; width: 100%; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 10px; font-size: 8px; }
        .sig-heading { font-weight: bold; margin-bottom: 28px; }
        .sig-line { border-top: 1px solid #111827; padding-top: 3px; text-align: center; }
        .sig-caption { font-size: 7px; color: #475569; margin-top: 2px; text-align: center; }
        .footer-note { margin-top: 10px; font-size: 7px; color: #64748b; }
    </style>
</head>
<body>
    <div class="letterhead">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Agency Logo" class="letterhead-logo">
        @endif
        <div class="agency-name">{{ $record->procuring_entity ?: $agency->displayName() }}</div>
        @if($agency->address)
            <div class="agency-meta">{{ $agency->address }}</div>
        @endif
    </div>

    <div class="form-title">Market Scoping Checklist</div>

    <div class="section-title">1. Agency Information</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Name of Procuring Entity</td>
            <td>{{ $record->procuring_entity ?: $agency->displayName() }}</td>
        </tr>
        <tr>
            <td class="meta-label">End-User / Implementing Unit</td>
            <td>{{ $record->end_user_unit ?: $record->division?->name ?: '—' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Name & Designation of Representative</td>
            <td>{{ trim(($record->representative_name ?: '—').($record->representative_designation ? ' / '.$record->representative_designation : '')) }}</td>
        </tr>
    </table>

    <div class="section-title">2. Project Overview</div>
    <table class="meta-table">
        <tr>
            <td class="meta-label">Project Name</td>
            <td colspan="3">{{ $record->project_name }}</td>
        </tr>
        <tr>
            <td class="meta-label">Estimated Budget</td>
            <td>₱{{ number_format((float) $record->estimated_budget, 2) }}</td>
            <td class="meta-label">Expected Date of Delivery (mm/yyyy)</td>
            <td>{{ $formatter::monthYear($record->expected_delivery) }}</td>
        </tr>
        <tr>
            <td class="meta-label">Period of Market Scoping<br>[From (mm/yyyy) To (mm/yyyy)]</td>
            <td colspan="3">{{ $formatter::periodRange($record) }}</td>
        </tr>
    </table>

    <div class="section-title">3. Market Scoping Activity/ies Conducted</div>
    <div class="section-note">
        This confirms that market scoping activities were conducted in accordance with Section 10 of Republic Act No. 12009 and its Implementing Rules and Regulations (IRR), and considered in the Project Procurement Management Plan, consistent with the Principle of Proportionality.
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="check-col">Check (✓)</th>
                <th class="param-col">Activity/ies Conducted</th>
                <th class="doc-col">Documentation (as may be applicable)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formatter::activityDefinitions() as $activity)
                <tr>
                    <td class="check-col">{{ $formatter::checkMark($formatter::activityChecked($record, $activity['key'])) }}</td>
                    <td>
                        {{ $activity['label'] }}
                        @if($activity['key'] === 'other' && data_get($record->activities, 'other.description'))
                            : {{ data_get($record->activities, 'other.description') }}
                        @endif
                    </td>
                    <td class="pre-wrap">{{ $activity['documentation_hint'] ?: $formatter::activityDocumentation($record, $activity['key']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="notes">
        <strong>Notes:</strong><br>
        i. The market scoping activities shall be identified and undertaken at the option of the End-User or Implementing Unit based on its needs and objectives.<br>
        ii. The list of supporting documents in the Documentation column is not exclusive and may include other documents that may be gathered by the End-User or Implementing Unit pertinent to the activity/ies conducted.
    </div>

    <div class="section-title">4. Market Scoping Results</div>
    <div class="section-note">
        Indicate recommendations in the column provided based on the results of the market scoping activities undertaken. These recommendations shall be considered in the development of a comprehensive and realistic PPMP, taking into account the parameters outlined under Section 10.4 of the IRR of RA 12009, as may be applicable.
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th class="param-col">Parameters</th>
                <th class="considered-col">Considered?<br>(Yes/No/Not Applicable)</th>
                <th class="recommend-col">Recommendations based on the Market Scoping<br>(Attach additional documents if necessary)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($formatter::parameterDefinitions() as $parameter)
                <tr>
                    <td>{{ $parameter['letter'] }}. {{ $parameter['label'] }}</td>
                    <td class="considered-col">{{ $formatter::parameterConsidered($record, $parameter['key']) }}</td>
                    <td class="pre-wrap">{{ $formatter::parameterRecommendations($record, $parameter['key']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-heading">Prepared by:</div>
                <div class="sig-line">{{ $record->preparedBy?->name ?? '_______________________________' }}</div>
                <div class="sig-caption">Personnel-in-Charge, End-User or Implementing Unit<br>[Signature over Printed Name / Position / Designation / Date]</div>
            </td>
            <td>
                <div class="sig-heading">Approved by:</div>
                <div class="sig-line">{{ $record->approvedBy?->name ?? '_______________________________' }}</div>
                <div class="sig-caption">Head, End-User or Implementing Unit<br>[Signature over Printed Name / Position / Designation / Date]</div>
            </td>
        </tr>
    </table>

    @if($verificationUrl)
        <table style="margin-top: 10px; width: 100%;">
            <tr>
                <td style="width: 12%; vertical-align: top;">
                    <x-qr-code :value="$verificationUrl" :size="56" />
                </td>
                <td style="width: 88%; vertical-align: middle; font-size: 7px; color: #64748b;">
                    Scan to verify this Market Scoping Checklist, or visit: {{ $verificationUrl }}
                </td>
            </tr>
        </table>
    @endif

    <div class="footer-note">
        Generated by {{ config('app.name') }} on {{ now()->format('F d, Y g:ia') }}.
        Form aligned with GPPB NGPA Market Scoping Checklist (RA 12009 IRR Section 10).
    </div>
</body>
</html>
