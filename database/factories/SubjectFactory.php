<?php

namespace Database\Factories;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Subject> */
class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Matematika', 'Bahasa Indonesia', 'Fisika', 'Biologi', 'Kimia']);

        return [
            'code' => strtoupper(fake()->unique()->lexify('???')).fake()->numberBetween(10, 99),
            'name' => $name,
            'is_active' => true,
        ];
    }
}
