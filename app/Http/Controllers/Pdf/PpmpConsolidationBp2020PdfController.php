<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\PpmpConsolidation;
use App\Models\Settings\AgencyProfile;
use App\Support\PpmpConsolidationBp202PrintFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PpmpConsolidationBp2020PdfController extends Controller
{
    public function __invoke(PpmpConsolidation $consolidation)
    {
        Gate::authorize('view', $consolidation);

        $consolidation->load([
            'bp2020Lines.consolidationItem.division',
            'bp2020Lines.consolidationItem.pap',
            'bp2020Lines.consolidationItem.fundSource',
            'fiscalYear',
        ]);

        $agency = AgencyProfile::current();

        $pdf = Pdf::loadView('pdf.ppmp-consolidation-bp2020', [
            'consolidation' => $consolidation,
            'agency' => $agency,
            'formTitle' => PpmpConsolidationBp202PrintFormatter::formTitle($consolidation),
            'profiles' => PpmpConsolidationBp202PrintFormatter::profiles($consolidation, $agency),
            'formatter' => PpmpConsolidationBp202PrintFormatter::class,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$consolidation->reference_no}-bp202.pdf");
    }
}
