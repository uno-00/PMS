<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Request — {{ $pr->pr_no }}</title>
    <style>
        @page { margin: 24px 28px 28px 28px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111827; line-height: 1.25; }
        table { border-collapse: collapse; width: 100%; }
        .letterhead { width: 100%; margin-bottom: 6px; }
        .letterhead td { vertical-align: top; padding: 0; }
        .letterhead-logo { height: 52px; max-width: 80px; object-fit: contain; }
        .letterhead-right { text-align: right; font-size: 8px; color: #334155; line-height: 1.35; }
        .appendix { text-align: right; font-size: 9px; font-weight: bold; margin: 2px 0 6px; }
        .form-title { text-align: center; font-size: 13px; font-weight: bold; text-transform: uppercase; margin: 4px 0 10px; letter-spacing: 0.5px; }
        .meta-table td { padding: 2px 4px 6px 0; vertical-align: bottom; font-size: 9px; }
        .meta-label { white-space: nowrap; padding-right: 4px; }
        .meta-value { border-bottom: 1px solid #111827; min-height: 14px; }
        .items-table th, .items-table td { border: 1px solid #111827; padding: 4px 5px; vertical-align: top; font-size: 8px; }
        .items-table th { font-weight: bold; text-align: center; background: #fff; }
        .col-stock { width: 11%; }
        .col-unit { width: 8%; text-align: center; }
        .col-desc { width: 36%; }
        .col-qty { width: 10%; text-align: center; }
        .col-cost { width: 17%; text-align: right; }
        .col-total { width: 18%; text-align: right; }
        .item-row td { height: 22px; }
        .total-row td { font-weight: bold; background: #f8fafc; }
        .purpose-block { margin-top: 8px; font-size: 9px; }
        .purpose-label { font-weight: bold; margin-bottom: 4px; }
        .purpose-text { border: 1px solid #111827; min-height: 48px; padding: 6px 8px; white-space: pre-wrap; }
        .signatures { margin-top: 12px; width: 100%; }
        .signatures td { width: 50%; vertical-align: top; padding: 0 10px; font-size: 9px; }
        .sig-heading { font-weight: bold; text-align: center; margin-bottom: 8px; text-transform: uppercase; }
        .sig-field { margin-bottom: 10px; }
        .sig-label { font-size: 8px; margin-bottom: 2px; }
        .sig-line { border-bottom: 1px solid #111827; min-height: 18px; padding-top: 12px; text-align: center; }
        .footer-note { margin-top: 14px; text-align: center; font-size: 8px; font-style: italic; color: #475569; }
        .pre-wrap { white-space: pre-wrap; }
    </style>
</head>
<body>
    <table class="letterhead">
        <tr>
            <td style="width: 55%;"></td>
            <td style="width: 45%;" class="letterhead-right">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Agency Logo" class="letterhead-logo"><br>
                @endif
                @if($agency->address)
                    {{ $agency->address }}<br>
                @endif
                @if($agency->website)
                    {{ $agency->website }}<br>
                @endif
                @if($agency->contact_phone)
                    {{ $agency->contact_phone }}
                @endif
            </td>
        </tr>
    </table>

    <div class="appendix">Appendix 60</div>
    <div class="form-title">Purchase Request</div>

    <table class="meta-table" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 12%;"><span class="meta-label">Entity Name:</span></td>
            <td style="width: 38%;" class="meta-value">{{ $formatter::entityName($agency) }}</td>
            <td style="width: 12%;"><span class="meta-label">Fund Cluster:</span></td>
            <td style="width: 38%;" class="meta-value">{{ $fundCluster }}</td>
        </tr>
    </table>

    <table class="meta-table" style="margin-bottom: 4px;">
        <tr>
            <td style="width: 14%;"><span class="meta-label">Office/Section:</span></td>
            <td style="width: 30%;" class="meta-value">{{ $officeSection }}</td>
            <td style="width: 8%;"><span class="meta-label">PR No.:</span></td>
            <td style="width: 22%;" class="meta-value">{{ $pr->pr_no }}</td>
            <td style="width: 8%;"><span class="meta-label">Date:</span></td>
            <td style="width: 18%;" class="meta-value">{{ $prDate }}</td>
        </tr>
        <tr>
            <td colspan="2" class="meta-value" style="border-bottom: 1px solid #111827;">&nbsp;</td>
            <td colspan="4">
                <span class="meta-label">Responsibility Center Code:</span>
                <span class="meta-value" style="display: inline-block; width: 68%; margin-left: 4px;">{{ $responsibilityCenterCode }}</span>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="col-stock">Stock/ Property No.</th>
                <th class="col-unit">Unit</th>
                <th class="col-desc">Item Description</th>
                <th class="col-qty">Quantity</th>
                <th class="col-cost">Unit Cost<br>(PhP)</th>
                <th class="col-total">Total Cost<br>(PhP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($itemRows as $row)
                <tr class="item-row">
                    <td class="col-stock">{{ $row['stock_property_no'] }}</td>
                    <td class="col-unit">{{ $row['unit'] }}</td>
                    <td class="col-desc pre-wrap">{{ $row['description'] }}</td>
                    <td class="col-qty">{{ $row['quantity'] }}</td>
                    <td class="col-cost">{{ $row['unit_cost'] }}</td>
                    <td class="col-total">{{ $row['total_cost'] }}</td>
                </tr>
            @endforeach
            @if($pr->total_amount > 0)
                <tr class="total-row">
                    <td colspan="5" style="text-align: right; padding-right: 8px;">TOTAL</td>
                    <td class="col-total">{{ number_format((float) $pr->total_amount, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="purpose-block">
        <div class="purpose-label">Purpose:</div>
        <div class="purpose-text">{{ $pr->purpose }}</div>
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-heading">Requested by:</div>
                <div class="sig-field">
                    <div class="sig-label">Signature:</div>
                    <div class="sig-line">&nbsp;</div>
                </div>
                <div class="sig-field">
                    <div class="sig-label">Printed Name:</div>
                    <div class="sig-line">{{ $requestedBy['name'] }}</div>
                </div>
                <div class="sig-field">
                    <div class="sig-label">Designation:</div>
                    <div class="sig-line">{{ $requestedBy['designation'] }}</div>
                </div>
            </td>
            <td>
                <div class="sig-heading">Approved by:</div>
                <div class="sig-field">
                    <div class="sig-label">Signature:</div>
                    <div class="sig-line">&nbsp;</div>
                </div>
                <div class="sig-field">
                    <div class="sig-label">Printed Name:</div>
                    <div class="sig-line">{{ $approvedBy['name'] }}</div>
                </div>
                <div class="sig-field">
                    <div class="sig-label">Designation:</div>
                    <div class="sig-line">{{ $approvedBy['designation'] }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">(PLEASE SEE BACK FOR INSTRUCTIONS)</div>
</body>
</html>
