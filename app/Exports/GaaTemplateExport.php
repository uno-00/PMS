<?php

namespace App\Exports;

use App\Models\Settings\Department;
use App\Models\Settings\Division;
use App\Models\Settings\FundSource;
use App\Models\Settings\Pap;
use App\Models\Settings\UacsCode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Generates the downloadable DBM GAA Excel template for end-users.
 *
 * Sheet 1 ("Line Items") carries the EXACT snake_case headers that
 * GaaLineItemsImport reads via WithHeadingRow, plus valid sample rows.
 * Sheet 2 ("Reference Codes") enumerates every currently configured
 * department/division/PAP/UACS/fund-source code so users know which
 * values the importer will accept — it is regenerated on every
 * download so it always reflects the live configuration.
 */
class GaaTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new GaaTemplateLineItemsSheet,
            new GaaTemplateReferenceCodesSheet,
        ];
    }
}

class GaaTemplateLineItemsSheet implements FromArray, ShouldAutoSize, WithColumnFormatting, WithEvents, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Line Items';
    }

    public function array(): array
    {
        return [
            // Row 1 — headers MUST match GaaLineItemsImport (WithHeadingRow).
            ['department_code', 'division_code', 'pap_code', 'uacs_code', 'fund_source_code', 'description', 'amount'],
            // Sample rows use live reference codes; overwrite or delete before upload.
            ['FMS', 'FMS-ACC', '310100100001000', '5020301000', '101', 'Office supplies — semestral stock', 250000.00],
            ['ITS', 'ITS-DEV', '310100100002000', '5060402000', '101', 'ICT equipment for systems development', 800000.00],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'B' => NumberFormat::FORMAT_TEXT,
            'C' => NumberFormat::FORMAT_TEXT,
            'D' => NumberFormat::FORMAT_TEXT,
            'E' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                foreach (['A', 'B', 'C', 'D', 'E'] as $column) {
                    for ($row = 2; $row <= 3; $row++) {
                        $cell = $sheet->getCell("{$column}{$row}");
                        $cell->setValueExplicit((string) $cell->getValue(), DataType::TYPE_STRING);
                    }
                }
            },
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A2:G3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '808080']],
        ]);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class GaaTemplateReferenceCodesSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function title(): string
    {
        return 'Reference Codes';
    }

    public function array(): array
    {
        $rows = [
            ['Reference', 'Code', 'Name / Description'],
        ];

        foreach (Department::query()->orderBy('code')->get() as $item) {
            $rows[] = ['Department', $item->code, $item->name];
        }

        foreach (Division::query()->with('department')->orderBy('code')->get() as $item) {
            $rows[] = ['Division', $item->code, $item->name.($item->department ? ' ('.$item->department->name.')' : '')];
        }

        foreach (Pap::query()->orderBy('code')->get() as $item) {
            $rows[] = ['PAP ('.ucfirst($item->type ?? 'n/a').')', $item->code, $item->name];
        }

        foreach (UacsCode::query()->orderBy('code')->get() as $item) {
            $rows[] = ['UACS Code', $item->code, $item->description.($item->expense_class ? ' ['.$item->expense_class.']' : '')];
        }

        foreach (FundSource::query()->orderBy('code')->get() as $item) {
            $rows[] = ['Fund Source', $item->code, $item->name];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
