<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum NoticeOfAwardStatus: string implements Transitionable
{
    case Awarded = 'awarded';
    case Accepted = 'accepted';
    case Declined = 'declined';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Awarded => [self::Accepted, self::Declined],
            self::Accepted, self::Declined => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Awarded => 'Awarded',
            self::Accepted => 'Accepted',
            self::Declined => 'Declined',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Awarded => 'amber',
            self::Accepted => 'emerald',
            self::Declined => 'red',
        };
    }
}
