<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>PPMP No. {{ $ppmpNumber }} — {{ $agency->displayName() }}</title>
    <style>
        @page { margin: 28px 24px 36px 24px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7px; color: #111827; line-height: 1.25; }
        table { border-collapse: collapse; width: 100%; }
        .letterhead { text-align: center; margin-bottom: 8px; }
        .letterhead-logo { height: 48px; max-width: 72px; object-fit: contain; margin-bottom: 4px; }
        .agency-name { font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .agency-meta { font-size: 7px; color: #475569; margin-top: 2px; }
        .form-title { text-align: center; font-size: 10px; font-weight: bold; text-transform: uppercase; margin: 8px 0 4px; }
        .form-subtitle { text-align: center; font-size: 7px; color: #475569; margin-bottom: 6px; }
        .meta-row td { padding: 3px 4px; vertical-align: top; font-size: 7px; }
        .meta-label { font-weight: bold; white-space: nowrap; padding-right: 4px; }
        .check-row { margin: 4px 0 6px; font-size: 7px; }
        .check-box { display: inline-block; width: 10px; text-align: center; font-weight: bold; }
        .ppmp-table th, .ppmp-table td { border: 1px solid #111827; padding: 3px; vertical-align: top; font-size: 6.5px; }
        .ppmp-table th { background: #f8fafc; font-weight: bold; text-align: center; }
        .group-header { background: #e2e8f0; font-size: 7px; font-weight: bold; text-align: center; text-transform: uppercase; }
        .col-no { width: 3%; text-align: center; font-weight: bold; }
        .col-desc { width: 14%; }
        .col-type { width: 8%; }
        .col-qty { width: 10%; }
        .col-mode { width: 9%; }
        .col-ppc { width: 5%; text-align: center; }
        .col-time { width: 6%; text-align: center; }
        .col-fund { width: 8%; }
        .col-budget { width: 8%; text-align: right; }
        .col-docs { width: 9%; }
        .col-remarks { width: 9%; }
        .total-row td { font-weight: bold; background: #f1f5f9; }
        .signatures { margin-top: 14px; width: 100%; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 8px; font-size: 7px; }
        .sig-heading { font-weight: bold; margin-bottom: 24px; }
        .sig-line { border-top: 1px solid #111827; padding-top: 3px; text-align: center; }
        .sig-caption { font-size: 6.5px; color: #475569; margin-top: 2px; text-align: center; }
        .footer-note { margin-top: 8px; font-size: 6px; color: #64748b; }
        .text-right { text-align: right; }
        .pre-wrap { white-space: pre-wrap; }
    </style>
</head>
<body>
    <div class="letterhead">
        @if($logoPath)
            <img src="{{ $logoPath }}" alt="Agency Logo" class="letterhead-logo">
        @endif
        <div class="agency-name">{{ $agency->displayName() }}</div>
        @if($agency->address)
            <div class="agency-meta">{{ $agency->address }}</div>
        @endif
        @if($agency->acronym)
            <div class="agency-meta">{{ $agency->acronym }}</div>
        @endif
    </div>

    <div class="form-title">Project Procurement Management Plan (PPMP) No. {{ $ppmpNumber }}</div>
    <div class="form-subtitle">IRR of RA No. 12009 — Sections 7.7.2 to 7.7.3</div>

    <div class="check-row">
        <span class="check-box">{{ $isFinal ? '☐' : '☑' }}</span> INDICATIVE &nbsp;&nbsp;&nbsp;
        <span class="check-box">{{ $isFinal ? '☑' : '☐' }}</span> FINAL
    </div>

    <table class="meta-row" style="margin-bottom: 6px;">
        <tr>
            <td style="width: 50%;"><span class="meta-label">Fiscal Year:</span> {{ $ppmp->fiscalYear?->year ?? '—' }}</td>
            <td style="width: 50%;"><span class="meta-label">End-User or Implementing Unit:</span> {{ $ppmp->division?->name ?? '—' }}</td>
        </tr>
        @if($ppmp->title)
            <tr>
                <td colspan="2"><span class="meta-label">PPMP Title:</span> {{ $ppmp->title }}</td>
            </tr>
        @endif
        <tr>
            <td><span class="meta-label">Control No.:</span> {{ $ppmp->control_no ?? '—' }}</td>
            <td><span class="meta-label">Status:</span> {{ $ppmp->status->label() }}</td>
        </tr>
    </table>

    <table class="ppmp-table">
        <thead>
            <tr>
                <th colspan="5" class="group-header">Procurement Project Details</th>
                <th colspan="3" class="group-header">Projected Timeline (MM/YYYY)</th>
                <th colspan="4" class="group-header">Funding Details</th>
            </tr>
            <tr>
                <th class="col-no">1</th>
                <th class="col-desc">General Description and Objective of the Project to be Procured</th>
                <th class="col-type">Type of the Project to be Procured (Goods / Infrastructure / Consulting Services)</th>
                <th class="col-qty">Quantity and Size of the Project to be Procured</th>
                <th class="col-mode">Recommended Mode of Procurement</th>
                <th class="col-ppc">Pre-Procurement Conference, if applicable (Yes/No)</th>
                <th class="col-time">Start of Procurement Activity</th>
                <th class="col-time">End of Procurement Activity</th>
                <th class="col-time">Expected Delivery / Implementation Period</th>
                <th class="col-fund">Source of Funds</th>
                <th class="col-budget">{{ $isFinal ? 'Authorized Budgetary Allocation (PhP)' : 'Estimated Budget (PhP)' }}</th>
                <th class="col-docs">Attached Supporting Document/s</th>
                <th class="col-remarks">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ppmp->items as $item)
                <tr>
                    <td class="col-no">{{ $item->item_no }}</td>
                    <td class="pre-wrap">{{ $formatter::generalDescription($item) }}</td>
                    <td class="pre-wrap">{{ $formatter::projectType($item) }}</td>
                    <td class="pre-wrap">{{ $formatter::quantityAndSize($item) }}</td>
                    <td>{{ $formatter::modeOfProcurement($item) }}</td>
                    <td class="col-ppc">{{ $formatter::preProcurementConference($item) }}</td>
                    <td>{{ $formatter::monthYear($item->schedule_start) }}</td>
                    <td>{{ $formatter::monthYear($item->schedule_end) }}</td>
                    <td>{{ $formatter::deliveryPeriod($item) }}</td>
                    <td>{{ $formatter::sourceOfFunds($item) }}</td>
                    <td class="text-right">{{ $formatter::budgetAmount($item) }}</td>
                    <td class="pre-wrap">{{ $formatter::supportingDocuments($item) }}</td>
                    <td class="pre-wrap">{{ $formatter::remarks($item) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; padding: 12px;">No procurement projects listed.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="10" class="text-right">TOTAL BUDGET:</td>
                <td class="text-right">₱{{ number_format((float) $ppmp->total_abc, 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-heading">Prepared by:</div>
                <div class="sig-line">{{ $ppmp->preparedBy?->name ?? '_______________________________' }}</div>
                <div class="sig-caption">Signature over Printed Name / Position / Designation<br>[End-User or Implementing Unit]</div>
                <div class="sig-caption" style="margin-top: 8px;">Date: ___________________</div>
            </td>
            <td>
                <div class="sig-heading">Submitted by:</div>
                <div class="sig-line">{{ $agency->head_of_agency ?? '_______________________________' }}</div>
                <div class="sig-caption">Signature over Printed Name / Position / Designation<br>[Head of the End-User or Implementing Unit]</div>
                <div class="sig-caption" style="margin-top: 8px;">Date: ___________________</div>
            </td>
        </tr>
    </table>

    @if($verificationUrl)
        <table style="margin-top: 10px; width: 100%;">
            <tr>
                <td style="width: 12%; vertical-align: top;">
                    <x-qr-code :value="$verificationUrl" :size="56" />
                </td>
                <td style="width: 88%; vertical-align: middle; font-size: 6px; color: #64748b;">
                    Scan to verify this PPMP, or visit: {{ $verificationUrl }}
                </td>
            </tr>
        </table>
    @endif

    <div class="footer-note">
        Generated by {{ config('app.name') }} on {{ now()->format('F d, Y g:ia') }}.
        Form aligned with GPPB NGPA PPMP (RA 12009 IRR Sec. 7.7.1–7.7.3).
    </div>
</body>
</html>
