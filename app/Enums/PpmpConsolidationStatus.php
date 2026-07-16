<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum PpmpConsolidationStatus: string implements Transitionable
{
    case Draft = 'draft';
    case Validation = 'validation';
    case PlanningReview = 'planning_review';
    case BudgetReview = 'budget_review';
    case AccountingReview = 'accounting_review';
    case BacReview = 'bac_review';
    case HopeReview = 'hope_review';
    case Approved = 'approved';
    case Locked = 'locked';
    case Cancelled = 'cancelled';
    case ReturnedForRevision = 'returned_for_revision';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Validation],
            self::Validation => [self::PlanningReview, self::Draft],
            self::PlanningReview => [self::BudgetReview, self::ReturnedForRevision],
            self::BudgetReview => [self::AccountingReview, self::ReturnedForRevision],
            self::AccountingReview => [self::BacReview, self::ReturnedForRevision],
            self::BacReview => [self::HopeReview, self::ReturnedForRevision],
            self::HopeReview => [self::Approved, self::ReturnedForRevision],
            self::Approved => [self::Locked],
            self::Locked => [],
            self::Cancelled => [],
            self::ReturnedForRevision => [self::Draft, self::Validation],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Validation => 'Review & Validation',
            self::PlanningReview => 'Planning Review',
            self::BudgetReview => 'Budget Review',
            self::AccountingReview => 'Accounting Review',
            self::BacReview => 'BAC Review',
            self::HopeReview => 'HoPE Review',
            self::Approved => 'Approved',
            self::Locked => 'Locked',
            self::Cancelled => 'Cancelled',
            self::ReturnedForRevision => 'Returned for Revision',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Validation => 'amber',
            self::PlanningReview => 'indigo',
            self::BudgetReview => 'purple',
            self::AccountingReview => 'cyan',
            self::BacReview => 'teal',
            self::HopeReview => 'sky',
            self::Approved => 'emerald',
            self::Locked => 'blue',
            self::Cancelled => 'red',
            self::ReturnedForRevision => 'red',
        };
    }

    public function isActive(): bool
    {
        return ! in_array($this, [self::Approved, self::Locked, self::Cancelled], true);
    }

    public function stepKey(): ?string
    {
        return match ($this) {
            self::PlanningReview => 'planning_review',
            self::BudgetReview => 'budget_validation',
            self::AccountingReview => 'accounting_review',
            self::BacReview => 'bac_consolidation',
            self::HopeReview => 'approval',
            default => null,
        };
    }
}
