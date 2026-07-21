<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum GaaStatus: string implements Transitionable
{
    case Draft = 'draft';
    case Validated = 'validated';
    case Approved = 'approved';
    case Distributed = 'distributed';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Validated],
            self::Validated => [self::Approved, self::Draft],
            self::Approved => [self::Distributed],
            self::Distributed => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Validated => 'Validated',
            self::Approved => 'Approved',
            self::Distributed => 'Distributed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Validated => 'amber',
            self::Approved => 'emerald',
            self::Distributed => 'blue',
        };
    }
}
