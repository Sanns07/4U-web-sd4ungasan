<?php

namespace App\Enums;

enum Religion: string
{
    case Islam = 'islam';
    case Christian = 'kristen';
    case Catholic = 'katolik';
    case Hindu = 'hindu';
    case Buddha = 'buddha';
    case Confucianism = 'konghucu';
    case Other = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Islam => 'Islam',
            self::Christian => 'Kristen',
            self::Catholic => 'Katolik',
            self::Hindu => 'Hindu',
            self::Buddha => 'Buddha',
            self::Confucianism => 'Konghucu',
            self::Other => 'Lainnya',
        };
    }
}
