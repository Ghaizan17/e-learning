<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeHistory>
 */
class GradeHistoryFactory extends Factory
{
    protected $model = GradeHistory::class;

    public function definition(): array
    {
        return [
            'grade_id' => Grade::factory(),
            'old_score' => fake()->randomFloat(2, 50, 80),
            'new_score' => fake()->randomFloat(2, 80, 100),
            'changed_by' => User::factory()->teacher(),
            'reason' => fake()->sentence(),
        ];
    }
}
