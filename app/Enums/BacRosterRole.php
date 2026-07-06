<?php

namespace App\Enums;

use App\Support\Roles;

enum BacRosterRole: string
{
    case Chairperson = 'chairperson';
    case Secretariat = 'secretariat';
    case Member = 'member';

    public function label(): string
    {
        return match ($this) {
            self::Chairperson => 'BAC Chairperson',
            self::Secretariat => 'BAC Secretariat',
            self::Member => 'BAC Member',
        };
    }

    public function spatieRole(): string
    {
        return match ($this) {
            self::Chairperson => Roles::BAC_CHAIRPERSON,
            self::Secretariat => Roles::BAC_SECRETARIAT,
            self::Member => Roles::BAC_MEMBER,
        };
    }

    public static function fromSpatieRole(string $roleName): ?self
    {
        return match ($roleName) {
            Roles::BAC_CHAIRPERSON => self::Chairperson,
            Roles::BAC_SECRETARIAT => self::Secretariat,
            Roles::BAC_MEMBER => self::Member,
            default => null,
        };
    }
}
