<?php

namespace App\Services\Planning;

use App\Enums\AnnualProcurementPlanStatus;
use App\Models\Planning\AnnualProcurementPlan;
use App\Models\User;

class AnnualProcurementPlanService
{
    public function consolidate(AnnualProcurementPlan $app, User $user): AnnualProcurementPlan
    {
        $app->recalculatePlannedAmount();
        $app->transitionTo(AnnualProcurementPlanStatus::ForConsolidation, 'Division PPMPs consolidated into the APP.', action: 'consolidated');

        return $app->fresh();
    }

    public function sendToBacReview(AnnualProcurementPlan $app, User $user): AnnualProcurementPlan
    {
        $app->update(['reviewed_by' => $user->id, 'reviewed_at' => now()]);
        $app->transitionTo(AnnualProcurementPlanStatus::BacReview, 'Forwarded to BAC for review.', action: 'submitted-for-bac-review');

        return $app->fresh();
    }

    public function approve(AnnualProcurementPlan $app, User $user, ?string $remarks = null): AnnualProcurementPlan
    {
        $app->update(['approved_by' => $user->id, 'approved_at' => now()]);
        $app->transitionTo(AnnualProcurementPlanStatus::Approved, $remarks, action: 'approved');

        return $app->fresh();
    }

    public function lock(AnnualProcurementPlan $app, User $user): AnnualProcurementPlan
    {
        $app->update(['locked_by' => $user->id, 'locked_at' => now()]);
        $app->transitionTo(AnnualProcurementPlanStatus::Locked, 'APP locked; no further PPMP changes accepted for this fiscal year.', action: 'locked');

        return $app->fresh();
    }

    public function unlock(AnnualProcurementPlan $app, User $user, ?string $remarks = null): AnnualProcurementPlan
    {
        $app->update(['locked_by' => null, 'locked_at' => null]);
        $app->transitionTo(
            AnnualProcurementPlanStatus::Approved,
            $remarks ?? 'APP unlocked; division PPMP updates may resume pending re-lock.',
            action: 'unlocked'
        );

        return $app->fresh();
    }
}
