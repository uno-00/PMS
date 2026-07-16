<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Settings\AgencyProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PpmpConsolidationWfpPdfController extends Controller
{
    public function __invoke(PpmpConsolidation $consolidation)
    {
        Gate::authorize('view', $consolidation);

        $consolidation->load(['wfpLines', 'fiscalYear']);

        $pdf = Pdf::loadView('pdf.ppmp-consolidation-wfp', [
            'consolidation' => $consolidation,
            'agency' => AgencyProfile::current(),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("{$consolidation->reference_no}-wfp.pdf");
    }
}
