<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Reads only the "Line Items" worksheet from the multi-sheet GAA template,
 * ignoring the "Reference Codes" lookup sheet.
 */
class GaaWorkbookImport implements WithMultipleSheets
{
    public function __construct(public GaaLineItemsImport $lineItems) {}

    public function sheets(): array
    {
        // Always read the first worksheet so uploads still work if the sheet
        // was renamed (e.g. "Sheet1") or when importing a CSV export.
        return [
            0 => $this->lineItems,
        ];
    }
}
