<?php

namespace Database\Factories;

use App\Enums\LessonType;
use App\Models\Chapter;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Lesson>
 */
class LessonFactory extends Factory
{
    protected $model = Lesson::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = 'Materi '.$this->faker->unique()->words(3, true);

        return [
            'chapter_id' => Chapter::factory(),
            'title' => Str::title($title),
            'slug' => Str::slug($title).'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'type' => LessonType::Text,
            'content_html' => '<p>'.$this->faker->paragraph().'</p>',
            'position' => 1,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['is_published' => true]);
    }

    public function video(): static
    {
        return $this->state(fn (): array => [
            'type' => LessonType::Video,
            'video_url' => 'https://www.youtube.com/watch?v='.$this->faker->lexify('???????????'),
            'content_html' => null,
        ]);
    }
}
