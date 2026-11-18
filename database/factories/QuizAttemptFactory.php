<?php

namespace Database\Factories;

use App\Enums\QuizAttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuizAttempt>
 */
class QuizAttemptFactory extends Factory
{
    protected $model = QuizAttempt::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory()->published(),
            'user_id' => User::factory()->student(),
            'attempt_no' => 1,
            'started_at' => now(),
            'expires_at' => now()->addHour(),
            'submitted_at' => null,
            'status' => QuizAttemptStatus::InProgress,
            'score' => null,
        ];
    }

    public function submitted(): static
    {
        return $this->state(fn (): array => [
            'status' => QuizAttemptStatus::Submitted,
            'submitted_at' => now(),
        ]);
    }

    public function graded(): static
    {
        return $this->state(fn (): array => [
            'status' => QuizAttemptStatus::Graded,
            'submitted_at' => now(),
            'score' => 80,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => QuizAttemptStatus::Expired,
            'submitted_at' => now(),
            'expires_at' => now()->subMinute(),
        ]);
    }
}
