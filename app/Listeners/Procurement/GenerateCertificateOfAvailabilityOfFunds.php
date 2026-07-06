<?php

namespace App\Listeners\Procurement;

use App\Events\Procurement\PurchaseRequestApproved;
use App\Services\Procurement\CafService;
use Illuminate\Contracts\Queue\ShouldQueue;

class GenerateCertificateOfAvailabilityOfFunds implements ShouldQueue
{
    public function __construct(protected CafService $caf) {}

    public function handle(PurchaseRequestApproved $event): void
    {
        $this->caf->generate($event->purchaseRequest);
    }
}
