<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum PurchaseRequestStatus: string implements Transitionable
{
    case Draft = 'draft';
    case DivisionChief = 'division_chief';
    case Planning = 'planning';
    case Budget = 'budget';
    case Hope = 'hope';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::DivisionChief, self::Cancelled],
            self::DivisionChief => [self::Planning, self::Rejected],
            self::Planning => [self::Budget, self::Rejected],
            self::Budget => [self::Hope, self::Rejected],
            self::Hope => [self::Approved, self::Rejected],
            self::Approved => [self::Cancelled],
            self::Rejected => [self::Draft],
            self::Cancelled => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::DivisionChief => 'Division Chief Review',
            self::Planning => 'Planning Review',
            self::Budget => 'Budget Review',
            self::Hope => 'HOPE Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::DivisionChief, self::Planning, self::Budget, self::Hope => 'amber',
            self::Approved => 'emerald',
            self::Rejected, self::Cancelled => 'red',
        };
    }
}
