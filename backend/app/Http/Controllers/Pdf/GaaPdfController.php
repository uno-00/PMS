<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Budget\GeneralAppropriationsAct;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class GaaPdfController extends Controller
{
    public function __invoke(GeneralAppropriationsAct $gaa)
    {
        Gate::authorize('view', $gaa);

        $summary = $gaa->summaryByDepartment();

        $pdf = Pdf::loadView('pdf.gaa', [
            'gaa' => $gaa,
            'summary' => $summary,
            'agency' => AgencyProfile::current(),
            'documentTitle' => 'General Appropriations Act - Budget Summary',
            'controlNo' => $gaa->reference_no ?? $gaa->title,
            'verificationUrl' => $gaa->reference_no ? DocumentVerification::urlFor('gaa', $gaa->reference_no) : null,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('GAA-'.$gaa->fiscalYear->year.'.pdf');
    }
}
