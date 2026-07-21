<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Settings\AgencyProfile;
use App\Support\DocumentVerification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class PurchaseOrderPdfController extends Controller
{
    public function __invoke(PurchaseOrder $purchaseOrder)
    {
        Gate::authorize('view', $purchaseOrder);

        $purchaseOrder->load(['items', 'bidder', 'modeOfProcurement']);

        $pdf = Pdf::loadView('pdf.purchase-order', [
            'po' => $purchaseOrder,
            'agency' => AgencyProfile::current(),
            'documentTitle' => 'Purchase Order',
            'controlNo' => $purchaseOrder->po_no,
            'verificationUrl' => DocumentVerification::urlFor('po', $purchaseOrder->po_no),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("{$purchaseOrder->po_no}.pdf");
    }
}
