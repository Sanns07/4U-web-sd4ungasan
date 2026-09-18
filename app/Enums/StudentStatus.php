<?php

namespace App\Enums;

enum StudentStatus: string
{
    case Active = 'aktif';
    case Alumni = 'alumni';
    case Archived = 'arsip';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Alumni => 'Alumni',
            self::Archived => 'Diarsipkan',
        };
    }
}
