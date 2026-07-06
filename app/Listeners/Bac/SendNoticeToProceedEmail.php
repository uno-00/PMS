<?php

namespace App\Listeners\Bac;

use App\Events\Bac\NoticeToProceedIssued;
use App\Notifications\Bac\NoticeToProceedIssuedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNoticeToProceedEmail implements ShouldQueue
{
    public function handle(NoticeToProceedIssued $event): void
    {
        $bidder = $event->noticeToProceed->procurement?->noticeOfAward?->bidder;
        $bidder?->notify(new NoticeToProceedIssuedNotification($event->noticeToProceed));
    }
}
