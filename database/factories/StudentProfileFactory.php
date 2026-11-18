<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->student(),
            'nisn' => fake()->unique()->numerify('############'),
            'nis' => fake()->unique()->numerify('#####'),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_place' => fake()->city(),
            'birth_date' => fake()->dateTimeBetween('-19 years', '-14 years')->format('Y-m-d'),
            'address' => fake()->address(),
            'guardian_name' => fake()->name(),
            'guardian_phone' => fake()->numerify('08##########'),
        ];
    }
}
