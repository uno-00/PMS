<?php

namespace App\Enums;

enum PpmpProjectType: string
{
    case Goods = 'goods';
    case Infrastructure = 'infrastructure';
    case Consulting = 'consulting';

    public function label(): string
    {
        return match ($this) {
            self::Goods => 'Goods',
            self::Infrastructure => 'Infrastructure',
            self::Consulting => 'Consulting Services',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public static function tryFromProposalText(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $haystack = strtolower($value);

        if (str_contains($haystack, 'consult')) {
            return self::Consulting;
        }

        if (str_contains($haystack, 'infrastructure') || str_contains($haystack, 'construction')) {
            return self::Infrastructure;
        }

        if (str_contains($haystack, 'goods')) {
            return self::Goods;
        }

        return null;
    }
}
