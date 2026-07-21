<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum AnnualProcurementPlanStatus: string implements Transitionable
{
    case Draft = 'draft';
    case ForConsolidation = 'for_consolidation';
    case BacReview = 'bac_review';
    case Approved = 'approved';
    case Locked = 'locked';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::ForConsolidation],
            self::ForConsolidation => [self::BacReview, self::Draft],
            self::BacReview => [self::Approved, self::ForConsolidation],
            self::Approved => [self::Locked],
            self::Locked => [self::Approved],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::ForConsolidation => 'For Consolidation',
            self::BacReview => 'BAC Review',
            self::Approved => 'Approved',
            self::Locked => 'Locked',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::ForConsolidation => 'amber',
            self::BacReview => 'indigo',
            self::Approved => 'emerald',
            self::Locked => 'blue',
        };
    }
}
