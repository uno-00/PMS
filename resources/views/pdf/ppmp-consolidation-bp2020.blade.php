<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $formTitle }} — {{ $consolidation->reference_no }}</title>
    <style>
        @page { margin: 18px 20px 28px 20px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 7px; color: #111827; line-height: 1.2; }
        table { border-collapse: collapse; width: 100%; }
        .form-title { text-align: center; font-size: 9px; font-weight: bold; margin-bottom: 2px; }
        .form-subtitle { text-align: center; font-size: 8px; font-weight: bold; margin-bottom: 8px; }
        .field-table td { border: 1px solid #111827; padding: 4px 6px; vertical-align: top; font-size: 7px; }
        .field-label { width: 34%; font-weight: normal; white-space: nowrap; }
        .field-value { width: 66%; min-height: 14px; }
        .field-value-tall { min-height: 28px; }
        .cat-grid { width: 100%; border-collapse: collapse; }
        .cat-grid td { border: 1px solid #111827; padding: 4px 6px; text-align: center; width: 25%; }
        .cat-header { font-size: 6.5px; font-weight: bold; }
        .cat-mark { font-weight: bold; font-size: 9px; }
        .impl-table td { border: 1px solid #111827; padding: 3px 5px; font-size: 6.5px; }
        .impl-label { width: 14%; font-weight: bold; }
        .impl-sub { width: 10%; text-align: center; font-weight: bold; }
        .section-heading { font-size: 7px; font-weight: bold; margin: 8px 0 4px; }
        .data-table th, .data-table td { border: 1px solid #111827; padding: 3px 4px; font-size: 6.5px; vertical-align: top; }
        .data-table th { font-weight: bold; text-align: center; background: #fff; }
        .col-letter { font-size: 6px; font-weight: normal; display: block; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .note { font-size: 6px; font-style: italic; margin: 3px 0; }
        .grand-row td { font-weight: bold; }
        .signature-table td { border: 1px solid #111827; padding: 6px 8px; vertical-align: top; font-size: 7px; width: 33.33%; height: 70px; }
        .signature-heading { font-weight: bold; margin-bottom: 28px; }
        .signature-role { font-size: 6.5px; margin-top: 4px; }
        .report-footer { margin-top: 8px; font-size: 6px; color: #475569; width: 100%; }
        .report-footer td { vertical-align: bottom; }
        .page-break { page-break-after: always; }
        .pre-wrap { white-space: pre-wrap; }
    </style>
</head>
<body>
@foreach($profiles as $profileIndex => $profile)
    @php($columns = $profile['columns'])
    @php($pageBase = ($profileIndex * 2) + 1)
    @php($totalPages = count($profiles) * 2)

    {{-- PAGE 1 --}}
    <div class="form-title">{{ $formTitle }}</div>
    <div class="form-subtitle">PROFILE FOR LOCALLY-FUNDED PROJECTS</div>

    <table class="field-table">
        <tr>
            <td class="field-label">1. Proposal/Project Name</td>
            <td class="field-value">{{ $profile['project_name'] }}</td>
        </tr>
        <tr>
            <td class="field-label">2. Implementing Department / Agency</td>
            <td class="field-value">{{ $profile['implementing_agency'] }}</td>
        </tr>
        <tr>
            <td class="field-label">3. Priority Ranking No.</td>
            <td class="field-value">{{ $profile['priority_ranking'] }}</td>
        </tr>
        <tr>
            <td class="field-label">4. Categorization</td>
            <td class="field-value" style="padding: 0;">
                <table class="cat-grid">
                    <tr>
                        <td class="cat-header">New</td>
                        <td class="cat-header">Infrastructure</td>
                        <td class="cat-header">Expanded/Revised</td>
                        <td class="cat-header">Non-Infrastructure</td>
                    </tr>
                    <tr>
                        <td class="cat-mark">{{ $formatter::checkMark($profile['is_new']) }}</td>
                        <td class="cat-mark">{{ $formatter::checkMark($profile['is_infrastructure']) }}</td>
                        <td class="cat-mark">{{ $formatter::checkMark($profile['is_expanded']) }}</td>
                        <td class="cat-mark">{{ $formatter::checkMark($profile['is_non_infrastructure']) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="field-label">5. PIP Code:</td>
            <td class="field-value">{{ $profile['pip_code'] }}</td>
        </tr>
        <tr>
            <td class="field-label">6. Total Proposal Cost ('000):</td>
            <td class="field-value">{{ $profile['total_cost_thousands'] }}</td>
        </tr>
        <tr>
            <td class="field-label">7. Description:</td>
            <td class="field-value field-value-tall pre-wrap">{{ $profile['description'] }}</td>
        </tr>
        <tr>
            <td class="field-label">8. Purpose:</td>
            <td class="field-value field-value-tall pre-wrap">{{ $profile['purpose'] }}</td>
        </tr>
        <tr>
            <td class="field-label">9. Beneficiaries:</td>
            <td class="field-value">{{ $profile['beneficiaries'] }}</td>
        </tr>
        <tr>
            <td class="field-label">10. Implementation Period:</td>
            <td class="field-value" style="padding: 0;">
                <table class="impl-table">
                    <tr>
                        <td class="impl-label"></td>
                        <td class="impl-sub">Start Date:</td>
                        <td class="impl-sub">Finish Date:</td>
                        <td class="impl-label"></td>
                        <td class="impl-sub">Start Date:</td>
                        <td class="impl-sub">Finish Date:</td>
                    </tr>
                    <tr>
                        <td class="impl-label">ORIGINAL</td>
                        <td>{{ $profile['original_start'] }}</td>
                        <td>{{ $profile['original_finish'] }}</td>
                        <td class="impl-label">REVISED</td>
                        <td>{{ $profile['revised_start'] }}</td>
                        <td>{{ $profile['revised_finish'] }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="field-label">11. Pre-Requisites:</td>
            <td class="field-value" style="padding: 0;">
                <table class="data-table">
                    <tr>
                        <th style="width: 28%;">Approving Authorities</th>
                        <th style="width: 12%;">Reviewed/Approved</th>
                        <th style="width: 8%;">Yes</th>
                        <th style="width: 8%;">No</th>
                        <th style="width: 14%;">Not Applicable</th>
                        <th style="width: 30%;">Remarks</th>
                    </tr>
                    <tr>
                        <td>DICT</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td class="text-center">X</td>
                        <td></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="section-heading">12. Financial (in P'000) and Physical Details</div>

    <div class="section-heading" style="margin-top: 4px;">12.1. PAP ATTRIBUTION BY EXPENSE CLASS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">PAP</th>
                <th style="width: 20%;">{{ $columns['tier2'] }}<span class="col-letter">(A)</span></th>
                <th style="width: 20%;">{{ $columns['year1'] }}<span class="col-letter">(B)</span></th>
                <th style="width: 20%;">{{ $columns['year2'] }}<span class="col-letter">(C)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $profile['pap_name'] }}</td>
                <td class="text-right">{{ $profile['pap_tier2'] }}</td>
                <td class="text-right">{{ $profile['pap_year1'] }}</td>
                <td class="text-right">{{ $profile['pap_year2'] }}</td>
            </tr>
            <tr>
                <td>CO</td>
                <td class="text-right">{{ $profile['co_tier2'] }}</td>
                <td class="text-right">{{ $profile['co_year1'] }}</td>
                <td class="text-right">{{ $profile['co_year2'] }}</td>
            </tr>
            <tr>
                <td>MOOE</td>
                <td class="text-right">{{ $profile['mooe_tier2'] }}</td>
                <td class="text-right">{{ $profile['mooe_year1'] }}</td>
                <td class="text-right">{{ $profile['mooe_year2'] }}</td>
            </tr>
            <tr class="grand-row">
                <td>GRAND TOTAL</td>
                <td class="text-right">{{ $profile['grand_tier2'] }}</td>
                <td class="text-right">{{ $profile['grand_year1'] }}</td>
                <td class="text-right">{{ $profile['grand_year2'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-heading">12.2. PHYSICAL ACCOMPLISHMENTS &amp; TARGETS</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 40%;">Physical Accomplishments</th>
                <th style="width: 20%;">Targets<br>{{ $columns['tier2'] }}<span class="col-letter">(A)</span></th>
                <th style="width: 20%;">{{ $columns['year1'] }}<span class="col-letter">(B)</span></th>
                <th style="width: 20%;">{{ $columns['year2'] }}<span class="col-letter">(C)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $profile['physical_description'] }}</td>
                <td class="text-right">{{ $profile['physical_tier2'] }}</td>
                <td class="text-right">{{ $profile['physical_year1'] }}</td>
                <td class="text-right">{{ $profile['physical_year2'] }}</td>
            </tr>
        </tbody>
    </table>

    <table class="report-footer">
        <tr>
            <td>This report was generated using {{ config('app.name') }} on {{ now()->format('F d, Y g:ia') }}; Status: {{ strtoupper($consolidation->status->label()) }}</td>
            <td class="text-right">Page {{ $pageBase }} of {{ $totalPages }}</td>
        </tr>
    </table>

    <div class="page-break"></div>

    {{-- PAGE 2 --}}
    <div class="section-heading">12.3. TOTAL PROJECT COST</div>
    <table class="data-table" style="width: 55%;">
        <thead>
            <tr>
                <th style="width: 55%;">Expense Class</th>
                <th style="width: 45%;">Total Project Cost</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>MOOE</td><td class="text-right">{{ $profile['total_mooe'] }}</td></tr>
            <tr><td>CO</td><td class="text-right">{{ $profile['total_co'] }}</td></tr>
            <tr class="grand-row"><td>GRAND TOTAL</td><td class="text-right">{{ $profile['total_grand'] }}</td></tr>
        </tbody>
    </table>

    <div class="section-heading">12.4. REQUIREMENTS FOR OPERATING COST OF INFRASTRUCTURE PROJECT</div>
    <div class="note">For Infrastructure projects, show the estimated ongoing operating costs to be included in Forward Estimates</div>
    <table class="data-table" style="width: 70%;">
        <thead>
            <tr>
                <th style="width: 40%;">PAP</th>
                <th style="width: 30%;">{{ $columns['year1'] }}<span class="col-letter">(A)</span></th>
                <th style="width: 30%;">{{ $columns['year2'] }}<span class="col-letter">(B)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr class="grand-row">
                <td>GRAND TOTAL</td>
                <td class="text-right">0.</td>
                <td class="text-right">0.</td>
            </tr>
        </tbody>
    </table>

    <div class="section-heading">12.5. COSTING BY COMPONENT(S)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 34%;">Components</th>
                <th style="width: 11%;">PS<span class="col-letter">(A)</span></th>
                <th style="width: 11%;">MOOE<span class="col-letter">(B)</span></th>
                <th style="width: 11%;">CO<span class="col-letter">(C)</span></th>
                <th style="width: 11%;">FINEX<span class="col-letter">(D)</span></th>
                <th style="width: 11%;">TOTAL<span class="col-letter">(E)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $profile['component_name'] }}</td>
                <td class="text-right">{{ $profile['component_ps'] }}</td>
                <td class="text-right">{{ $profile['component_mooe'] }}</td>
                <td class="text-right">{{ $profile['component_co'] }}</td>
                <td class="text-right">{{ $profile['component_finex'] }}</td>
                <td class="text-right">{{ $profile['component_total'] }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="text-right">0.</td>
                <td class="text-right">0.</td>
                <td class="text-right">0.</td>
                <td class="text-right">0.</td>
                <td class="text-right">0.</td>
            </tr>
            <tr class="grand-row">
                <td>GRAND TOTAL</td>
                <td class="text-right">{{ $profile['component_ps'] }}</td>
                <td class="text-right">{{ $profile['component_mooe'] }}</td>
                <td class="text-right">{{ $profile['component_co'] }}</td>
                <td class="text-right">{{ $profile['component_finex'] }}</td>
                <td class="text-right">{{ $profile['component_total'] }}</td>
            </tr>
        </tbody>
    </table>

    <div class="section-heading">12.6. LOCATION OF IMPLEMENTATION</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 34%;">Location</th>
                <th style="width: 11%;">PS<span class="col-letter">(A)</span></th>
                <th style="width: 11%;">MOOE<span class="col-letter">(B)</span></th>
                <th style="width: 11%;">CO<span class="col-letter">(C)</span></th>
                <th style="width: 11%;">FINEX<span class="col-letter">(D)</span></th>
                <th style="width: 11%;">TOTAL<span class="col-letter">(E)</span></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $profile['location'] }}</td>
                <td class="text-right">{{ $profile['location_ps'] }}</td>
                <td class="text-right">{{ $profile['location_mooe'] }}</td>
                <td class="text-right">{{ $profile['location_co'] }}</td>
                <td class="text-right">{{ $profile['location_finex'] }}</td>
                <td class="text-right">{{ $profile['location_total'] }}</td>
            </tr>
            <tr class="grand-row">
                <td>GRAND TOTAL</td>
                <td class="text-right">{{ $profile['location_ps'] }}</td>
                <td class="text-right">{{ $profile['location_mooe'] }}</td>
                <td class="text-right">{{ $profile['location_co'] }}</td>
                <td class="text-right">{{ $profile['location_finex'] }}</td>
                <td class="text-right">{{ $profile['location_total'] }}</td>
            </tr>
        </tbody>
    </table>

    <table class="signature-table" style="margin-top: 10px;">
        <tr>
            <td>
                <div class="signature-heading">Prepared and Certified Correct:</div>
                <div class="signature-role">End-user</div>
            </td>
            <td>
                <div class="signature-heading">Approved:</div>
                <div class="signature-role">Agency Head / Director - General</div>
            </td>
            <td>
                <div class="signature-heading">Date:</div>
                <div class="signature-role">DAY/MO/YEAR</div>
            </td>
        </tr>
    </table>

    <table class="report-footer">
        <tr>
            <td>This report was generated using {{ config('app.name') }} on {{ now()->format('F d, Y g:ia') }}; Status: {{ strtoupper($consolidation->status->label()) }}</td>
            <td class="text-right">Page {{ $pageBase + 1 }} of {{ $totalPages }}</td>
        </tr>
    </table>

    @if(! $loop->last)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
