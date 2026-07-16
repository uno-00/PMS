<?php

namespace App\Http\Controllers;

use App\Exports\GenericCollectionExport;
use App\Models\Planning\PpmpConsolidation;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PpmpConsolidationExportController extends Controller
{
    public function __invoke(PpmpConsolidation $consolidation, string $format = 'excel'): BinaryFileResponse
    {
        Gate::authorize('export', $consolidation);

        $consolidation->load(['items.division', 'bp2020Lines', 'wfpLines']);

        $headings = ['Section', 'Reference', 'Description', 'Division/Office', 'Quantity', 'Unit', 'Unit Cost', 'Amount', 'Fund Source'];
        $rows = collect();

        foreach ($consolidation->items as $item) {
            $rows->push([
                'Consolidated PPMP',
                $consolidation->reference_no,
                $item->item_name,
                $item->division?->name,
                $item->quantity,
                $item->unit,
                $item->estimated_unit_cost,
                $item->lineAbc(),
                $item->fundSource?->name,
            ]);
        }

        foreach ($consolidation->bp2020Lines as $line) {
            $rows->push([
                'BP Form 2020',
                $consolidation->reference_no,
                $line->procurement_item,
                $line->activity,
                $line->quantity,
                $line->unit,
                $line->unit_cost,
                $line->budget_allocation,
                $line->fund_source,
            ]);
        }

        foreach ($consolidation->wfpLines as $line) {
            $rows->push([
                'WFP',
                $consolidation->reference_no,
                $line->activity,
                $line->responsible_office,
                '',
                '',
                '',
                $line->budget_allocation,
                $line->funding_source,
            ]);
        }

        return Excel::download(
            new GenericCollectionExport($headings, $rows, 'PPMP Consolidation'),
            "{$consolidation->reference_no}-consolidation.xlsx"
        );
    }
}
