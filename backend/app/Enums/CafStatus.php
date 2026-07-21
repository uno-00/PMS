<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum CafStatus: string implements Transitionable
{
    case Generated = 'generated';
    case Certified = 'certified';
    case Approved = 'approved';
    case Printed = 'printed';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Generated => [self::Certified],
            self::Certified => [self::Approved],
            self::Approved => [self::Printed],
            self::Printed => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Generated',
            self::Certified => 'Certified',
            self::Approved => 'Approved',
            self::Printed => 'Printed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Generated => 'slate',
            self::Certified => 'amber',
            self::Approved => 'emerald',
            self::Printed => 'blue',
        };
    }
}
