<?php

namespace App\Enums;

enum TwGDesignationType: string
{
    case Primary = 'primary';
    case Alternate = 'alternate';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primary',
            self::Alternate => 'Alternate',
        };
    }
}
