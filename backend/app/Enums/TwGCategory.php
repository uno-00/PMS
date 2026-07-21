<?php

namespace App\Enums;

enum TwGCategory: string
{
    case Infrastructure = 'infrastructure';
    case Equipment = 'equipment';
    case Services = 'services';

    public function label(): string
    {
        return match ($this) {
            self::Infrastructure => 'Infrastructure',
            self::Equipment => 'Equipment',
            self::Services => 'Services',
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
