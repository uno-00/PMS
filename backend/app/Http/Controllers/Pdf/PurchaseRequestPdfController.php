<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Settings\AgencyProfile;
use App\Support\PurchaseRequestPrintFormatter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PurchaseRequestPdfController extends Controller
{
    public function __invoke(PurchaseRequest $purchaseRequest)
    {
        Gate::authorize('view', $purchaseRequest);

        $purchaseRequest->load([
            'items.ppmpItem.fundSource',
            'items.ppmpItem.budgetAllocation.costCenter',
            'division',
            'fiscalYear',
            'requestedBy',
            'hopeApprover',
        ]);

        $agency = AgencyProfile::current();
        $logoPath = null;

        if ($agency->logo_path) {
            $absolute = public_path('storage/'.str_replace('\\', '/', $agency->logo_path));

            if (is_file($absolute)) {
                $logoPath = $absolute;
            }
        }

        $formatter = PurchaseRequestPrintFormatter::class;
        $itemRows = $formatter::padItemRows($formatter::itemRows($purchaseRequest));

        $pdf = Pdf::loadView('pdf.purchase-request', [
            'pr' => $purchaseRequest,
            'agency' => $agency,
            'logoPath' => $logoPath,
            'formatter' => $formatter,
            'itemRows' => $itemRows,
            'fundCluster' => $formatter::fundCluster($purchaseRequest),
            'officeSection' => $formatter::officeSection($purchaseRequest),
            'prDate' => $formatter::prDate($purchaseRequest),
            'responsibilityCenterCode' => $formatter::responsibilityCenterCode($purchaseRequest),
            'requestedBy' => $formatter::requestedBy($purchaseRequest),
            'approvedBy' => $formatter::approvedBy($purchaseRequest, $agency),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$purchaseRequest->pr_no}.pdf");
    }
}
