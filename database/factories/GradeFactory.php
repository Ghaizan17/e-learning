<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        $score = fake()->randomFloat(2, 50, 100);
        $max = 100;

        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory()->student(),
            'grade_component_id' => GradeComponent::factory(),
            'score' => $score,
            'max_score' => $max,
            'percentage' => round($score / $max * 100, 2),
            'graded_by' => null,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(fn (): array => ['is_published' => false]);
    }
}
