<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Settings\AgencyProfile;
use App\Support\PpmpConsolidationPrintFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PpmpConsolidationPdfController extends Controller
{
    public function __invoke(PpmpConsolidation $consolidation)
    {
        Gate::authorize('view', $consolidation);

        $consolidation->load([
            'items.division',
            'items.modeOfProcurement',
            'items.fundSource',
            'items.uacsCode',
            'sourcePpmps',
            'fiscalYear',
            'creator',
        ]);

        $agency = AgencyProfile::current();
        $logoPath = null;

        if ($agency->logo_path) {
            $absolute = public_path('storage/'.str_replace('\\', '/', $agency->logo_path));

            if (is_file($absolute)) {
                $logoPath = $absolute;
            }
        }

        $pdf = Pdf::loadView('pdf.ppmp-consolidation', [
            'consolidation' => $consolidation,
            'agency' => $agency,
            'logoPath' => $logoPath,
            'ppmpNumber' => PpmpConsolidationPrintFormatter::ppmpNumber($consolidation),
            'isFinal' => PpmpConsolidationPrintFormatter::isFinal($consolidation),
            'formatter' => PpmpConsolidationPrintFormatter::class,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("{$consolidation->reference_no}-consolidated-ppmp.pdf");
    }
}
