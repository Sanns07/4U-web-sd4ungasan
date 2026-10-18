<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SchoolClass> */
class SchoolClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_period_id' => AcademicPeriod::factory(),
            'name' => fake()->unique()->randomElement(['X-1', 'X-2', 'XI IPA 1', 'XI IPS 1']),
            'grade_level' => 11,
            'room' => 'Ruang '.fake()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
