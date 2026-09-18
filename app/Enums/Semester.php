<?php

namespace App\Enums;

enum Semester: string
{
    case Odd = 'ganjil';
    case Even = 'genap';

    public function label(): string
    {
        return match ($this) {
            self::Odd => 'Ganjil',
            self::Even => 'Genap',
        };
    }
}
