<?php

namespace Database\Factories;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory()->published(),
            'user_id' => User::factory()->student(),
            'attempt_no' => 1,
            'is_latest' => true,
            'content' => $this->faker->paragraph(),
            'status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
        ];
    }

    public function late(): static
    {
        return $this->state(fn (): array => ['status' => SubmissionStatus::Late]);
    }

    public function graded(): static
    {
        return $this->state(fn (): array => ['status' => SubmissionStatus::Graded]);
    }
}
