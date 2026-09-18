<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case Active = 'aktif';
    case Completed = 'selesai';
    case Transferred = 'pindah';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Completed => 'Selesai',
            self::Transferred => 'Pindah',
        };
    }
}
