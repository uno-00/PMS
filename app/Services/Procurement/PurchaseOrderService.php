<?php

namespace App\Services\Procurement;

use App\Enums\ProcurementCaseStatus;
use App\Enums\PurchaseOrderStatus;
use App\Models\Bac\Procurement;
use App\Models\Procurement\Acceptance;
use App\Models\Procurement\Delivery;
use App\Models\Procurement\Inspection;
use App\Models\Procurement\Payment;
use App\Models\Procurement\PurchaseOrder;
use App\Models\User;

/**
 * Phase 17. Applicable to every mode of procurement: for Small Value
 * Procurement / Shopping / Direct Contracting the PO is issued directly
 * off the approved Purchase Request (no bidding case required); for
 * Public Bidding it is issued after NTP. Also owns the trailing
 * Delivery -> Inspection -> Acceptance -> Payment chain.
 */
class PurchaseOrderService
{
    public function create(array $attributes, User $user): PurchaseOrder
    {
        $po = PurchaseOrder::query()->create(array_merge($attributes, [
            'status' => PurchaseOrderStatus::Draft,
            'prepared_by' => $user->id,
        ]));

        return $po;
    }

    public function approve(PurchaseOrder $po, User $user): PurchaseOrder
    {
        $po->update(['approved_by' => $user->id, 'approved_at' => now()]);
        $po->transitionTo(PurchaseOrderStatus::Approved, 'PO approved.');

        $po->procurement?->transitionTo(ProcurementCaseStatus::PoIssued, 'Purchase Order issued.');

        return $po->fresh();
    }

    public function recordDelivery(PurchaseOrder $po, User $user, array $attributes): Delivery
    {
        $delivery = $po->deliveries()->create(array_merge($attributes, ['received_by' => $user->id]));
        $po->transitionTo(PurchaseOrderStatus::Delivered, 'Delivery recorded.');

        return $delivery;
    }

    public function recordInspection(Delivery $delivery, User $user, string $result, ?string $remarks = null): Inspection
    {
        $inspection = $delivery->inspection()->updateOrCreate([], [
            'inspected_by' => $user->id,
            'inspection_date' => now(),
            'result' => $result,
            'remarks' => $remarks,
        ]);

        if ($result === 'passed') {
            $delivery->purchaseOrder->transitionTo(PurchaseOrderStatus::Inspected, 'Inspection passed.');
        }

        return $inspection;
    }

    public function recordAcceptance(Delivery $delivery, User $user, ?string $remarks = null): Acceptance
    {
        $acceptance = $delivery->acceptance()->updateOrCreate([], [
            'accepted_by' => $user->id,
            'accepted_date' => now(),
            'remarks' => $remarks,
        ]);

        $delivery->purchaseOrder->transitionTo(PurchaseOrderStatus::Accepted, 'Goods/services accepted.');

        $delivery->purchaseOrder->procurement?->transitionTo(ProcurementCaseStatus::Completed, 'Procurement cycle completed upon acceptance.', enforce: false);

        return $acceptance;
    }

    public function recordPayment(PurchaseOrder $po, User $user, array $attributes): Payment
    {
        $payment = $po->payments()->create(array_merge($attributes, ['processed_by' => $user->id]));

        if ($po->status === PurchaseOrderStatus::Accepted) {
            $po->transitionTo(PurchaseOrderStatus::Invoiced, 'Invoice/payment recorded.');
        }

        if (($attributes['status'] ?? null) === 'released') {
            $po->transitionTo(PurchaseOrderStatus::Paid, 'Payment released.');
        }

        return $payment;
    }
}
