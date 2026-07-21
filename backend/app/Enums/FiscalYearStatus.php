<?php

namespace App\Enums;

enum FiscalYearStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Closed => 'Closed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'slate',
            self::Active => 'emerald',
            self::Closed => 'red',
        };
    }
}
