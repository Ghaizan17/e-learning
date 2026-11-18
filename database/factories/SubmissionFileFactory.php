<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubmissionFile>
 */
class SubmissionFileFactory extends Factory
{
    protected $model = SubmissionFile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'file_path' => 'submissions/'.$this->faker->uuid().'.pdf',
            'original_name' => 'tugas.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => $this->faker->numberBetween(10_000, 1_000_000),
        ];
    }
}
