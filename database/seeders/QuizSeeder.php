<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Quiz;
use App\Services\Learning\QuestionService;
use App\Services\Learning\QuizService;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Seed a realistic quiz for the demo course (non-production only).
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $course = Course::query()->where('title', 'Pemrograman Web Dasar')->first();

        if ($course === null) {
            return;
        }

        $quiz = Quiz::query()->firstOrCreate(
            ['course_id' => $course->getKey(), 'title' => 'Kuis HTML & CSS Dasar'],
            [
                'description' => 'Kuis singkat untuk menguji pemahaman struktur HTML dan dasar CSS.',
                'type' => 'quiz',
                'duration_minutes' => 15,
                'max_attempts' => 2,
                'passing_score' => 70,
                'show_result_immediately' => true,
                'is_published' => false,
            ],
        );

        if ($quiz->questions()->exists()) {
            if (! $quiz->is_published) {
                app(QuizService::class)->publish($quiz);
            }

            return;
        }

        $questions = app(QuestionService::class);

        $questions->create($quiz, [
            'type' => 'multiple_choice',
            'question_text' => 'Tag HTML manakah yang digunakan untuk membuat tautan?',
            'score' => 20,
            'explanation' => 'Tag <a> dengan atribut href membuat hyperlink.',
            'options' => [
                ['option_text' => '<a>', 'is_correct' => true],
                ['option_text' => '<p>', 'is_correct' => false],
                ['option_text' => '<img>', 'is_correct' => false],
                ['option_text' => '<div>', 'is_correct' => false],
            ],
        ]);

        $questions->create($quiz, [
            'type' => 'true_false',
            'question_text' => 'CSS digunakan untuk mengatur tampilan halaman web.',
            'score' => 20,
            'options' => [
                ['option_text' => 'Benar', 'is_correct' => true],
                ['option_text' => 'Salah', 'is_correct' => false],
            ],
        ]);

        $questions->create($quiz, [
            'type' => 'multiple_select',
            'question_text' => 'Manakah yang termasuk elemen semantik HTML5?',
            'score' => 30,
            'options' => [
                ['option_text' => '<header>', 'is_correct' => true],
                ['option_text' => '<main>', 'is_correct' => true],
                ['option_text' => '<footer>', 'is_correct' => true],
                ['option_text' => '<font>', 'is_correct' => false],
            ],
        ]);

        $questions->create($quiz, [
            'type' => 'essay',
            'question_text' => 'Jelaskan perbedaan antara margin dan padding pada CSS.',
            'score' => 30,
            'explanation' => 'Margin berada di luar batas elemen, padding di dalam batas elemen.',
        ]);

        app(QuizService::class)->publish($quiz);

        $this->command?->info('Quiz seeded.');
    }
}
