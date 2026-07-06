<?php

namespace App\Listeners\Budget;

use App\Enums\AnnualProcurementPlanStatus;
use App\Events\Budget\GaaApproved;
use App\Models\Planning\AnnualProcurementPlan;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Phase 2 rule: "After GAA approval, automatically create the APP."
 * The new APP starts in Draft, inherits the approved GAA total as its
 * budget ceiling, and is linked back to the source GAA for traceability.
 */
class CreateAnnualProcurementPlanFromGaa implements ShouldQueue
{
    public function handle(GaaApproved $event): void
    {
        AnnualProcurementPlan::query()->firstOrCreate(
            ['fiscal_year_id' => $event->gaa->fiscal_year_id],
            [
                'gaa_id' => $event->gaa->id,
                'reference_no' => 'APP-'.$event->gaa->fiscalYear->year,
                'total_budget' => $event->gaa->total_amount,
                'status' => AnnualProcurementPlanStatus::Draft,
            ]
        );
    }
}
