<?php

namespace App\Enums;

enum PreProcurementConference: string
{
    case Yes = 'yes';
    case No = 'no';
    case NotApplicable = 'na';

    public function label(): string
    {
        return match ($this) {
            self::Yes => 'Yes',
            self::No => 'No',
            self::NotApplicable => 'N/A',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        return [
            '' => 'Auto (based on mode of procurement)',
            self::Yes->value => self::Yes->label(),
            self::No->value => self::No->label(),
            self::NotApplicable->value => self::NotApplicable->label(),
        ];
    }
}
