<?php

namespace App\Enums;

enum MarketScopingConsidered: string
{
    case Yes = 'yes';
    case No = 'no';
    case NotApplicable = 'na';

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::NotApplicable => 'Not Applicable',
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
