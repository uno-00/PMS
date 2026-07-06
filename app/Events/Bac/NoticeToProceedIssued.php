<?php

namespace App\Events\Bac;

use App\Models\Procurement\NoticeToProceed;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoticeToProceedIssued
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public NoticeToProceed $noticeToProceed) {}
}
