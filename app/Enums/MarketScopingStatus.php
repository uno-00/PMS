<?php

namespace App\Enums;

enum MarketScopingStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Approved => 'Approved',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'amber',
            self::Approved => 'emerald',
        };
    }
}
