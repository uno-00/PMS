<?php

namespace App\Events\Bac;

use App\Models\Bac\PhilgepsPosting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PhilgepsPostingPublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PhilgepsPosting $posting) {}
}
