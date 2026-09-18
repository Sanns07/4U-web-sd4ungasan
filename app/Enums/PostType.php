<?php

namespace App\Enums;

enum PostType: string
{
    case News = 'berita';
    case Announcement = 'pengumuman';

    public function label(): string
    {
        return match ($this) {
            self::News => 'Berita',
            self::Announcement => 'Pengumuman',
        };
    }
}
