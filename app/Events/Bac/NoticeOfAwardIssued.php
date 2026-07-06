<?php

namespace App\Events\Bac;

use App\Models\Procurement\NoticeOfAward;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticeOfAwardIssued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public NoticeOfAward $noticeOfAward) {}
}
