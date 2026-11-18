<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssignmentAttachment>
 */
class AssignmentAttachmentFactory extends Factory
{
    protected $model = AssignmentAttachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'title' => 'Lampiran '.$this->faker->word(),
            'file_path' => 'assignment-attachments/'.$this->faker->uuid().'.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => $this->faker->numberBetween(20_000, 2_000_000),
        ];
    }
}
