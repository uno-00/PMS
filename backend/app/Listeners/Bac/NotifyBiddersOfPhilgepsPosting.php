<?php

namespace App\Listeners\Bac;

use App\Events\Bac\PhilgepsPostingPublished;
use App\Models\Supplier\Bidder;
use App\Notifications\Bac\PhilgepsPostingPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyBiddersOfPhilgepsPosting implements ShouldQueue
{
    public function handle(PhilgepsPostingPublished $event): void
    {
        $bidders = Bidder::query()->where('status', 'verified')->whereNotNull('email')->get();

        if ($bidders->isNotEmpty()) {
            Notification::send($bidders, new PhilgepsPostingPublishedNotification($event->posting));
        }
    }
}
