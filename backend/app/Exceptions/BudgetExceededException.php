<?php

namespace App\Exceptions;

use Exception;

/**
 * Raised whenever an operation would allocate, obligate, or utilize more
 * funds than are actually available at some level of the budget hierarchy
 * (GAA -> Budget Allocation -> PPMP -> Purchase Request). Enforcing this
 * as a first-class exception (rather than a silent clamp) is what
 * guarantees the "No overallocation allowed" rule everywhere in the system.
 */
class BudgetExceededException extends Exception
{
    public static function forAllocation(float $requested, float $available): self
    {
        return new self(sprintf(
            'Requested amount of %s exceeds the available balance of %s.',
            number_format($requested, 2),
            number_format($available, 2)
        ));
    }
}
