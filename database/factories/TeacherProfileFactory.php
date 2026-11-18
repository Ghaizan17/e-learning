<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->teacher(),
            'nip' => fake()->unique()->numerify('##################'),
            'nuptk' => fake()->unique()->numerify('################'),
            'gender' => fake()->randomElement(['male', 'female']),
            'address' => fake()->address(),
            'employment_status' => fake()->randomElement(['permanent', 'contract', 'honorary']),
            'joined_at' => fake()->dateTimeBetween('-10 years', '-1 year')->format('Y-m-d'),
        ];
    }
}
