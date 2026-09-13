<?php

return [
    'items' => [
        'islam' => [
            ['id' => 'subuh', 'label' => 'Subuh'],
            ['id' => 'zuhur', 'label' => 'Zuhur'],
            ['id' => 'asar', 'label' => 'Asar'],
            ['id' => 'magrib', 'label' => 'Magrib'],
            ['id' => 'isya', 'label' => 'Isya'],
            ['id' => 'mengaji', 'label' => "Mengaji / Membaca Al-Qur'an"],
        ],
        'kristen' => [
            ['id' => 'doa_pagi', 'label' => 'Doa Pagi / Saat Teduh'],
            ['id' => 'doa_makan', 'label' => 'Doa Makan'],
            ['id' => 'doa_malam', 'label' => 'Doa Malam'],
            ['id' => 'membaca_alkitab', 'label' => 'Membaca Alkitab'],
            ['id' => 'ibadah_minggu', 'label' => 'Ibadah Minggu / Sekolah Minggu', 'day_of_week' => [0]], // 0 = Sunday
        ],
        'katolik' => [
            ['id' => 'doa_pagi', 'label' => 'Doa Pagi'],
            ['id' => 'doa_angelus', 'label' => 'Doa Malaikat Tuhan (Angelus)'],
            ['id' => 'doa_makan', 'label' => 'Doa Makan'],
            ['id' => 'doa_malam', 'label' => 'Doa Malam'],
            ['id' => 'membaca_kitab_suci', 'label' => 'Membaca Kitab Suci'],
            ['id' => 'misa_minggu', 'label' => 'Misa / Sekolah Minggu', 'day_of_week' => [0, 6]], // 0 = Sunday, 6 = Saturday
        ],
        'hindu' => [
            ['id' => 'trisandya_pagi', 'label' => 'Trisandya Pagi'],
            ['id' => 'trisandya_siang', 'label' => 'Trisandya Siang'],
            ['id' => 'trisandya_sore', 'label' => 'Trisandya Sore'],
            ['id' => 'mebanten', 'label' => 'Mebanten / Mejanten'],
            ['id' => 'sembahyang_pura', 'label' => 'Sembahyang Pura / Doa Sehari-hari'],
        ],
        'buddha' => [
            ['id' => 'puja_bakti_pagi', 'label' => 'Puja Bakti Pagi'],
            ['id' => 'puja_bakti_malam', 'label' => 'Puja Bakti Malam'],
            ['id' => 'meditasi', 'label' => 'Meditasi'],
            ['id' => 'membaca_paritta', 'label' => 'Membaca Paritta'],
        ],
        'konghucu' => [
            ['id' => 'sembahyang_pagi', 'label' => 'Sembahyang Pagi'],
            ['id' => 'sembahyang_malam', 'label' => 'Sembahyang Malam'],
            ['id' => 'kebaktian', 'label' => 'Kebaktian'],
        ],
        'lainnya' => [
            ['id' => 'doa_pagi', 'label' => 'Doa Pagi / Refleksi Pagi'],
            ['id' => 'doa_malam', 'label' => 'Doa Malam / Refleksi Malam'],
            ['id' => 'ibadah_harian', 'label' => 'Ibadah / Kegiatan Rohani'],
        ],
    ],
];
