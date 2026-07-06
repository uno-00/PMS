<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Bac\Procurement;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class NoticeToProceedPdfController extends Controller
{
    public function __invoke(Procurement $procurement)
    {
        Gate::authorize('view', $procurement);

        $ntp = $procurement->noticeToProceed()->with('issuedBy')->firstOrFail();
        $noa = $procurement->noticeOfAward;

        $pdf = Pdf::loadView('pdf.ntp', [
            'ntp' => $ntp,
            'procurement' => $procurement,
            'noa' => $noa,
            'agency' => AgencyProfile::current(),
            'documentTitle' => 'Notice to Proceed',
            'controlNo' => $ntp->ntp_no,
            'verificationUrl' => DocumentVerification::urlFor('ntp', $ntp->ntp_no),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$ntp->ntp_no}.pdf");
    }
}
