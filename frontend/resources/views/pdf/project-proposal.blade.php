<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $record->control_no }} — Project Proposal</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        .header { width: 100%; margin-bottom: 16px; }
        .header td { vertical-align: top; }
        .title-box { border: 1px solid #333; padding: 8px; text-align: center; font-weight: bold; }
        .section-title { font-weight: bold; margin: 14px 0 6px; }
        table.info { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table.info td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        table.info td.label { width: 28%; background: #eee; font-weight: bold; }
        .narrative { border: 1px solid #333; min-height: 60px; padding: 8px; margin-bottom: 10px; }
        .narrative table { width: 100%; border-collapse: collapse; margin: 6px 0; font-size: 10px; }
        .narrative th, .narrative td { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
        .narrative th { background: #eee; font-weight: bold; }
        .narrative p { margin: 0 0 4px; }
        .narrative ul, .narrative ol { margin: 4px 0 4px 16px; padding: 0; }
        .signatories { margin-top: 24px; width: 100%; }
        .signatories td { width: 33%; text-align: center; vertical-align: top; padding-top: 30px; }
        .sig-name { font-weight: bold; text-transform: uppercase; border-top: 1px solid #333; display: inline-block; min-width: 180px; padding-top: 4px; }
        .sig-title { font-size: 10px; margin-top: 4px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width: 60%;">
                @if($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" style="height: 48px; margin-bottom: 6px;">
                @endif
                <div style="font-size: 10px;">PAMBANSANG MUSEO NG PILIPINAS</div>
                <div style="font-size: 10px;">NATIONAL MUSEUM OF THE PHILIPPINES</div>
            </td>
            <td style="width: 40%;">
                <div class="title-box">
                    PROJECT PROPOSAL<br>
                    <span style="font-size: 10px; font-weight: normal;">Document Reference No.: {{ $record->document_ref }}</span><br>
                    <span style="font-size: 10px; font-weight: normal;">With enclosures: {{ $record->with_enclosures ?: '—' }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">I. Basic Information</div>
    <table class="info">
        <tr><td class="label">Type of Project</td><td>{{ $record->project_type }}</td></tr>
        <tr><td class="label">Title</td><td>{{ $record->title }}</td></tr>
        <tr><td class="label">Schedule</td><td>{{ $record->schedule ?: '—' }}</td></tr>
        <tr><td class="label">Venue / Area</td><td>{{ $record->venue_area ?: '—' }}</td></tr>
        <tr><td class="label">Total Cost</td><td>₱{{ number_format($record->total_cost, 2) }}</td></tr>
        <tr><td class="label">Fund Source</td><td>{{ $record->fund_source_text ?: '—' }}</td></tr>
        <tr><td class="label">Proponent</td><td>{{ $record->proponent ?: '—' }}</td></tr>
    </table>

    <div class="section-title">II. Rationale</div>
    <div class="narrative rich-text-content">{!! \App\Support\RichTextSanitizer::clean($record->rationale) ?: ' ' !!}</div>

    <div class="section-title">III. Objectives</div>
    <div class="narrative rich-text-content">{!! \App\Support\RichTextSanitizer::clean($record->objectives) ?: ' ' !!}</div>

    <div class="section-title">IV. Target Schedule for the Project</div>
    <div class="narrative rich-text-content">{!! \App\Support\RichTextSanitizer::clean($record->target_schedule) ?: ' ' !!}</div>

    <div class="section-title">V. Budgetary Requirement</div>
    <div class="narrative rich-text-content">{!! \App\Support\RichTextSanitizer::clean($record->budgetary_requirement) ?: ' ' !!}</div>

    <div class="section-title">VI. Fund Source</div>
    <div class="narrative rich-text-content">{!! \App\Support\RichTextSanitizer::clean($record->fund_source_narrative) ?: ' ' !!}</div>

    <table class="signatories">
        <tr>
            <td>
                <div class="sig-name">{{ $record->preparedBy?->name ?? ' ' }}</div>
                <div class="sig-title">Prepared by</div>
            </td>
            <td>
                <div class="sig-name">{{ $record->recommendedBy?->name ?? ' ' }}</div>
                <div class="sig-title">Recommending Approval</div>
            </td>
            <td>
                <div class="sig-name">{{ $record->approvedBy?->name ?? ' ' }}</div>
                <div class="sig-title">Approved</div>
            </td>
        </tr>
    </table>
</body>
</html>
