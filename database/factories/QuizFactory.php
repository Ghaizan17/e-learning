<?php

namespace Database\Factories;

use App\Enums\QuizType;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Quiz>
 */
class QuizFactory extends Factory
{
    protected $model = Quiz::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => 'Kuis '.$this->faker->unique()->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => QuizType::Quiz,
            'duration_minutes' => 60,
            'starts_at' => null,
            'ends_at' => null,
            'max_attempts' => 1,
            'passing_score' => 70,
            'shuffle_questions' => false,
            'shuffle_options' => false,
            'show_result_immediately' => true,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['is_published' => true]);
    }

    public function exam(): static
    {
        return $this->state(fn (): array => ['type' => QuizType::Exam, 'duration_minutes' => 90]);
    }
}
