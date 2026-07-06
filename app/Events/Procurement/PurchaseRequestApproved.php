<?php

namespace App\Events\Procurement;

use App\Models\Procurement\PurchaseRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PurchaseRequestApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PurchaseRequest $purchaseRequest) {}
}
