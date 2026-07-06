<?php

namespace App\Support\Workflow;

use Exception;

class InvalidTransitionException extends Exception
{
    public static function make(Transitionable $from, Transitionable $to): self
    {
        return new self("Cannot transition from [{$from->label()}] to [{$to->label()}].");
    }
}
