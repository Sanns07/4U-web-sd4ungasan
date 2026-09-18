<?php

namespace App\Enums;

enum DayOfWeek: string
{
    case Monday = 'senin';
    case Tuesday = 'selasa';
    case Wednesday = 'rabu';
    case Thursday = 'kamis';
    case Friday = 'jumat';
    case Saturday = 'sabtu';

    public function label(): string
    {
        return match ($this) {
            self::Monday => 'Senin',
            self::Tuesday => 'Selasa',
            self::Wednesday => 'Rabu',
            self::Thursday => 'Kamis',
            self::Friday => 'Jumat',
            self::Saturday => 'Sabtu',
        };
    }

    public function order(): int
    {
        return array_search($this, self::cases(), true);
    }
}
