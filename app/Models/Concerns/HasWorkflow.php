<?php

namespace App\Models\Concerns;

use App\Models\WorkflowHistory;
use App\Support\Workflow\InvalidTransitionException;
use App\Support\Workflow\Transitionable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

/**
 * Gives any model with a `status` column (backed by a Transitionable enum)
 * the ability to move through its workflow while automatically writing an
 * immutable entry to workflow_histories. This is the single choke point
 * every phase (GAA, APP, PPMP, PR, CAF, BAC, bidding, NOA, NTP, PO) uses
 * to change state, guaranteeing a complete, consistent audit trail.
 */
trait HasWorkflow
{
    public function workflowHistories(): MorphMany
    {
        return $this->morphMany(WorkflowHistory::class, 'workflowable')->latest('performed_at');
    }

    /**
     * @param  Transitionable  $to  target status
     * @param  string|null  $action  short verb describing the transition, e.g. "submitted", "approved". Defaults to the target status label.
     * @param  bool  $enforce  whether to validate against allowedTransitions() of the current status
     */
    public function transitionTo(Transitionable $to, ?string $remarks = null, array $metadata = [], ?string $action = null, bool $enforce = true): static
    {
        /** @var Transitionable|null $from */
        $from = $this->status;

        if ($enforce && $from !== null) {
            $allowed = $from->allowedTransitions();
            if (! empty($allowed) && ! in_array($to, $allowed, true) && $to !== $from) {
                throw InvalidTransitionException::make($from, $to);
            }
        }

        $this->status = $to;
        $this->save();

        $this->workflowHistories()->create([
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'action' => $action ?? $to->label(),
            'remarks' => $remarks,
            'performed_by' => Auth::id(),
            'performed_role' => Auth::user()?->getRoleNames()->first(),
            'metadata' => $metadata,
            'performed_at' => now(),
        ]);

        return $this;
    }
}
