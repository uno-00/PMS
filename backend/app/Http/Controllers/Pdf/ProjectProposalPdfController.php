<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Planning\ProjectProposal;
use App\Models\Settings\AgencyProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ProjectProposalPdfController extends Controller
{
    public function __invoke(ProjectProposal $projectProposal)
    {
        Gate::authorize('view', $projectProposal);

        $projectProposal->load(['division', 'fiscalYear', 'preparedBy', 'recommendedBy', 'approvedBy', 'marketScoping']);

        $agency = AgencyProfile::current();
        $logoPath = null;

        if ($agency->logo_path) {
            $absolute = public_path('storage/'.str_replace('\\', '/', $agency->logo_path));

            if (is_file($absolute)) {
                $logoPath = $absolute;
            }
        }

        $pdf = Pdf::loadView('pdf.project-proposal', [
            'record' => $projectProposal,
            'agency' => $agency,
            'logoPath' => $logoPath,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$projectProposal->control_no}.pdf");
    }
}
