<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Teacher> */
class TeacherFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nip' => fake()->unique()->numerify('##################'),
            'name' => fake()->name(),
            'gender' => fake()->randomElement(Gender::cases()),
            'email' => fake()->unique()->safeEmail(),
            'public_title' => 'Guru '.fake()->randomElement(['Matematika', 'Bahasa', 'Sains']),
            'is_public' => true,
            'is_active' => true,
        ];
    }
}
