<?php

namespace App\Services\Supplier;

use App\Models\Bac\Procurement;
use App\Models\Supplier\Bidder;
use App\Models\Supplier\BidDocumentOrder;

/**
 * Phase 10: online request/purchase of bidding documents.
 */
class BidDocumentOrderService
{
    public function order(Procurement $procurement, Bidder $bidder, float $amount): BidDocumentOrder
    {
        return BidDocumentOrder::query()->firstOrCreate(
            ['procurement_id' => $procurement->id, 'bidder_id' => $bidder->id],
            ['amount' => $amount, 'payment_status' => 'pending']
        );
    }

    public function markPaid(BidDocumentOrder $order, string $orNo): BidDocumentOrder
    {
        $order->markPaid($orNo);

        return $order->fresh();
    }

    public function canDownload(BidDocumentOrder $order): bool
    {
        return $order->payment_status === 'paid';
    }
}
