<?php

namespace Database\Factories;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $position = 0;

        return [
            'quiz_id' => Quiz::factory(),
            'type' => QuestionType::MultipleChoice,
            'question_text' => $this->faker->sentence().'?',
            'score' => 10,
            'explanation' => null,
            'position' => ++$position,
        ];
    }

    public function essay(): static
    {
        return $this->state(fn (): array => ['type' => QuestionType::Essay]);
    }
}
