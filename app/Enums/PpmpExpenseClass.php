<?php

namespace App\Enums;

enum PpmpExpenseClass: string
{
    case Mooe = 'mooe';
    case CapitalOutlay = 'capital_outlay';

    public function label(): string
    {
        return match ($this) {
            self::Mooe => 'MOOE',
            self::CapitalOutlay => 'Capital Outlay',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }
}
