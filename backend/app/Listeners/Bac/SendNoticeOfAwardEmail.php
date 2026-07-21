<?php

namespace App\Listeners\Bac;

use App\Events\Bac\NoticeOfAwardIssued;
use App\Notifications\Bac\NoticeOfAwardIssuedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNoticeOfAwardEmail implements ShouldQueue
{
    public function handle(NoticeOfAwardIssued $event): void
    {
        $event->noticeOfAward->bidder?->notify(new NoticeOfAwardIssuedNotification($event->noticeOfAward));
    }
}
