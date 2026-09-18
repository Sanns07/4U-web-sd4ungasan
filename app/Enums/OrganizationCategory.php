<?php

namespace App\Enums;

enum OrganizationCategory: string
{
    case Principal = 'kepala_sekolah';
    case Committee = 'komite';
    case Administration = 'tata_usaha';
    case Other = 'lainnya';

    public function label(): string
    {
        return match ($this) {
            self::Principal => 'Kepala Sekolah',
            self::Committee => 'Komite',
            self::Administration => 'Tata Usaha',
            self::Other => 'Lainnya',
        };
    }
}
