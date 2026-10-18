<?php

namespace Database\Factories;

use App\Enums\Semester;
use App\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AcademicPeriod> */
class AcademicPeriodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_year' => '2026/2027',
            'semester' => Semester::Odd,
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'is_active' => true,
        ];
    }
}
