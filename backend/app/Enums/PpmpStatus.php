<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum PpmpStatus: string implements Transitionable
{
    case Draft = 'draft';
    case DivisionChiefReview = 'division_chief_review';
    case PlanningReview = 'planning_review';
    case BacConsolidation = 'bac_consolidation';
    case ProcurementModeReview = 'procurement_mode_review';
    case BudgetValidation = 'budget_validation';
    case Approved = 'approved';
    case Locked = 'locked';
    case ReturnedForRevision = 'returned_for_revision';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::DivisionChiefReview],
            self::DivisionChiefReview => [self::PlanningReview, self::ReturnedForRevision],
            self::PlanningReview => [self::BacConsolidation, self::ReturnedForRevision],
            self::BacConsolidation => [self::ProcurementModeReview, self::ReturnedForRevision],
            self::ProcurementModeReview => [self::BudgetValidation, self::ReturnedForRevision],
            self::BudgetValidation => [self::Approved, self::ReturnedForRevision],
            self::Approved => [self::Locked],
            self::Locked => [],
            self::ReturnedForRevision => [self::Draft, self::DivisionChiefReview],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::DivisionChiefReview => 'Division Chief Review',
            self::PlanningReview => 'Planning Review',
            self::BacConsolidation => 'BAC Consolidation',
            self::ProcurementModeReview => 'Procurement Mode Review',
            self::BudgetValidation => 'Budget Validation',
            self::Approved => 'Approved',
            self::Locked => 'Locked',
            self::ReturnedForRevision => 'Returned for Revision',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::DivisionChiefReview => 'amber',
            self::PlanningReview => 'indigo',
            self::BacConsolidation => 'cyan',
            self::ProcurementModeReview => 'teal',
            self::BudgetValidation => 'purple',
            self::Approved => 'emerald',
            self::Locked => 'blue',
            self::ReturnedForRevision => 'red',
        };
    }

    /** Maps a status to the approval_routings step_key used to resolve the responsible role. */
    public function stepKey(): ?string
    {
        return match ($this) {
            self::DivisionChiefReview => 'division_chief_review',
            self::PlanningReview => 'planning_review',
            self::BacConsolidation => 'bac_consolidation',
            self::ProcurementModeReview => 'procurement_mode_review',
            self::BudgetValidation => 'budget_validation',
            self::Approved => 'approval',
            default => null,
        };
    }
}
