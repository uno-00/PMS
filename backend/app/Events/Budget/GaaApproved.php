<?php

namespace App\Events\Budget;

use App\Models\Budget\GeneralAppropriationsAct;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GaaApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public GeneralAppropriationsAct $gaa) {}
}
