<?php

namespace App\Enums;

enum PpmpDocumentType: string
{
    case Indicative = 'indicative';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Indicative => 'Indicative',
            self::Final => 'Final',
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
