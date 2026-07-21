<?php

namespace App\Http\Controllers\Pdf;

use App\Enums\CafStatus;
use App\Http\Controllers\Controller;
use App\Models\Procurement\CertificateOfAvailabilityOfFunds;
use App\Models\Settings\AgencyProfile;
use App\Services\Procurement\CafService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class CafPdfController extends Controller
{
    public function __invoke(CertificateOfAvailabilityOfFunds $caf)
    {
        Gate::authorize('view', $caf);

        $caf->load(['purchaseRequest.division', 'purchaseRequest.fiscalYear', 'fundSource', 'uacsCode', 'generatedBy', 'certifiedBy', 'approvedBy']);

        $pdf = Pdf::loadView('pdf.caf', [
            'caf' => $caf,
            'agency' => AgencyProfile::current(),
            'documentTitle' => 'Certificate of Availability of Funds',
            'controlNo' => $caf->caf_no,
            'verificationUrl' => $caf->verificationUrl(),
        ])->setPaper('a4', 'portrait');

        if ($caf->status === CafStatus::Approved) {
            app(CafService::class)->markPrinted($caf);
        }

        return $pdf->stream("{$caf->caf_no}.pdf");
    }
}
