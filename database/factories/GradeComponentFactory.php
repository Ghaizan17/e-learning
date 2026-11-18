<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\GradeComponent;
use App\Models\GradingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeComponent>
 */
class GradeComponentFactory extends Factory
{
    protected $model = GradeComponent::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'grading_category_id' => GradingCategory::factory(),
            'gradeable_type' => null,
            'gradeable_id' => null,
            'title' => fake()->sentence(3),
            'max_score' => 100,
        ];
    }
}
