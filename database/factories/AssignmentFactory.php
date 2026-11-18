<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'Tugas '.$this->faker->unique()->words(3, true),
            'instructions' => $this->faker->paragraphs(2, true),
            'due_at' => now()->addWeek(),
            'max_score' => 100,
            'allow_resubmission' => false,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['is_published' => true]);
    }

    public function overdue(): static
    {
        return $this->state(fn (): array => ['due_at' => now()->subDay()]);
    }

    public function allowResubmission(): static
    {
        return $this->state(fn (): array => ['allow_resubmission' => true]);
    }
}
