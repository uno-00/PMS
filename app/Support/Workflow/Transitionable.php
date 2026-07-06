<?php

namespace App\Support\Workflow;

/**
 * Contract for any enum representing a document workflow status
 * (GAA, APP, PPMP, Purchase Request, CAF, NOA, NTP, PO, etc).
 *
 * Implementations must declare the finite set of allowed forward
 * transitions so that services can validate state changes generically
 * through WorkflowManager instead of scattering `if` chains everywhere.
 */
interface Transitionable
{
    /**
     * @return array<int, self> statuses this status is allowed to move to.
     */
    public function allowedTransitions(): array;

    public function label(): string;

    /**
     * Tailwind color token used for status badges across the UI.
     */
    public function color(): string;
}
