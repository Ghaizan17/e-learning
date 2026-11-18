<?php

namespace Database\Factories;

use App\Enums\GradingCategoryName;
use App\Models\Course;
use App\Models\GradingCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingCategory>
 */
class GradingCategoryFactory extends Factory
{
    protected $model = GradingCategory::class;

    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'name' => GradingCategoryName::Assignment,
            'weight' => 25,
        ];
    }

    public function named(GradingCategoryName $name, float $weight): static
    {
        return $this->state(fn (): array => [
            'name' => $name,
            'weight' => $weight,
        ]);
    }
}
