<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\Ppmp;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use App\Support\PpmpPrintFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PpmpPdfController extends Controller
{
    public function __invoke(Ppmp $ppmp)
    {
        Gate::authorize('view', $ppmp);

        $ppmp->load([
            'items.modeOfProcurement',
            'items.fundSource',
            'items.uacsCode',
            'division',
            'fiscalYear',
            'preparedBy',
        ]);

        $agency = AgencyProfile::current();
        $logoPath = null;

        if ($agency->logo_path) {
            $absolute = public_path('storage/'.str_replace('\\', '/', $agency->logo_path));

            if (is_file($absolute)) {
                $logoPath = $absolute;
            }
        }

        $pdf = Pdf::loadView('pdf.ppmp', [
            'ppmp' => $ppmp,
            'agency' => $agency,
            'logoPath' => $logoPath,
            'ppmpNumber' => PpmpPrintFormatter::ppmpNumber($ppmp),
            'isFinal' => PpmpPrintFormatter::isFinal($ppmp),
            'formatter' => PpmpPrintFormatter::class,
            'verificationUrl' => DocumentVerification::urlFor('ppmp', $ppmp->control_no),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("{$ppmp->control_no}.pdf");
    }
}
