<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\SubmissionGrade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionGrade>
 */
class SubmissionGradeFactory extends Factory
{
    protected $model = SubmissionGrade::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory()->graded(),
            'graded_by' => User::factory()->teacher(),
            'score' => $this->faker->numberBetween(60, 100),
            'feedback' => $this->faker->sentence(),
            'graded_at' => now(),
        ];
    }
}
