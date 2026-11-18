<?php

namespace Database\Factories;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LessonAttachment>
 */
class LessonAttachmentFactory extends Factory
{
    protected $model = LessonAttachment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lesson_id' => Lesson::factory(),
            'title' => 'Lampiran '.$this->faker->word(),
            'file_path' => 'lesson-attachments/'.$this->faker->uuid().'.pdf',
            'disk' => 'local',
            'mime_type' => 'application/pdf',
            'size_bytes' => $this->faker->numberBetween(50_000, 5_000_000),
        ];
    }
}
