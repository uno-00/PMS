<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\MarketScoping;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use App\Support\MarketScopingPrintFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class MarketScopingPdfController extends Controller
{
    public function __invoke(MarketScoping $marketScoping)
    {
        Gate::authorize('view', $marketScoping);

        $marketScoping->load(['division', 'fiscalYear', 'preparedBy', 'approvedBy']);

        $agency = AgencyProfile::current();
        $logoPath = null;

        if ($agency->logo_path) {
            $absolute = public_path('storage/'.str_replace('\\', '/', $agency->logo_path));

            if (is_file($absolute)) {
                $logoPath = $absolute;
            }
        }

        $pdf = Pdf::loadView('pdf.market-scoping', [
            'record' => $marketScoping,
            'agency' => $agency,
            'logoPath' => $logoPath,
            'formatter' => MarketScopingPrintFormatter::class,
            'verificationUrl' => DocumentVerification::urlFor('market-scoping', $marketScoping->control_no),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$marketScoping->control_no}.pdf");
    }
}
