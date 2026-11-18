<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => fake()->unique()->words(2, true),
            'code' => strtoupper(fake()->unique()->bothify('???-###')),
            'description' => fake()->sentence(),
        ];
    }

    public function general(): static
    {
        return $this->state(fn (array $attributes): array => [
            'department_id' => null,
        ]);
    }
}
