<?php

namespace App\Enums;

use App\Support\Workflow\Transitionable;

enum PhilgepsPostingStatus: string implements Transitionable
{
    case Published = 'published';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Published => [self::Closed, self::Cancelled],
            self::Closed => [],
            self::Cancelled => [],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Published => 'Published',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Published => 'emerald',
            self::Closed => 'slate',
            self::Cancelled => 'red',
        };
    }
}
