<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = strtoupper(fake()->unique()->bothify('???'));

        return [
            'name' => 'Program Keahlian '.$code,
            'code' => $code,
            'description' => fake()->sentence(),
        ];
    }
}
