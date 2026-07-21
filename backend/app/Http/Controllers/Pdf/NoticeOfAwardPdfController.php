<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Bac\Procurement;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class NoticeOfAwardPdfController extends Controller
{
    public function __invoke(Procurement $procurement)
    {
        Gate::authorize('view', $procurement);

        $noa = $procurement->noticeOfAward()->with(['bidder', 'approvedBy'])->firstOrFail();

        $pdf = Pdf::loadView('pdf.noa', [
            'noa' => $noa,
            'procurement' => $procurement,
            'agency' => AgencyProfile::current(),
            'documentTitle' => 'Notice of Award',
            'controlNo' => $noa->noa_no,
            'verificationUrl' => DocumentVerification::urlFor('noa', $noa->noa_no),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$noa->noa_no}.pdf");
    }
}
