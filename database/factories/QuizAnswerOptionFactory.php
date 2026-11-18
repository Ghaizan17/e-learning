<?php

namespace Database\Factories;

use App\Models\QuestionOption;
use App\Models\QuizAnswer;
use App\Models\QuizAnswerOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAnswerOption>
 */
class QuizAnswerOptionFactory extends Factory
{
    protected $model = QuizAnswerOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_answer_id' => QuizAnswer::factory(),
            'question_option_id' => QuestionOption::factory(),
        ];
    }
}
