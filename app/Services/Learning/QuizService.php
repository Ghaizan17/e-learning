<?php

namespace App\Services\Learning;

use App\Enums\QuestionType;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\Grading\GradeComponentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizService
{
    public function __construct(private readonly GradeComponentService $components) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(Course $course, array $data): Quiz
    {
        return DB::transaction(fn (): Quiz => $course->quizzes()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? 'quiz',
            'duration_minutes' => $data['duration_minutes'] ?? 60,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? 1,
            'passing_score' => $data['passing_score'] ?? 70,
            'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
            'shuffle_options' => (bool) ($data['shuffle_options'] ?? false),
            'show_result_immediately' => (bool) ($data['show_result_immediately'] ?? true),
            'is_published' => false,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Quiz $quiz, array $data): Quiz
    {
        $this->assertWindow($data['starts_at'] ?? null, $data['ends_at'] ?? null);

        $quiz->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'] ?? $quiz->type->value,
            'duration_minutes' => $data['duration_minutes'] ?? $quiz->duration_minutes,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'max_attempts' => $data['max_attempts'] ?? $quiz->max_attempts,
            'passing_score' => $data['passing_score'] ?? $quiz->passing_score,
            'shuffle_questions' => (bool) ($data['shuffle_questions'] ?? false),
            'shuffle_options' => (bool) ($data['shuffle_options'] ?? false),
            'show_result_immediately' => (bool) ($data['show_result_immediately'] ?? false),
        ]);

        return $quiz->refresh();
    }

    public function publish(Quiz $quiz): Quiz
    {
        $this->assertPublishable($quiz);

        $quiz->update(['is_published' => true]);

        $this->components->syncQuiz($quiz);

        return $quiz->refresh();
    }

    public function unpublish(Quiz $quiz): Quiz
    {
        $quiz->update(['is_published' => false]);

        return $quiz->refresh();
    }

    public function delete(Quiz $quiz): void
    {
        DB::transaction(fn () => $quiz->delete());
    }

    public function assertPublishable(Quiz $quiz): void
    {
        $this->assertWindow($quiz->starts_at?->toDateTimeString(), $quiz->ends_at?->toDateTimeString());

        $questions = $quiz->questions()->with('options')->get();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'questions' => 'Tambahkan minimal satu soal sebelum menerbitkan.',
            ]);
        }

        foreach ($questions as $question) {
            if (! $question->type->requiresOptions()) {
                continue;
            }

            $correct = $question->options->where('is_correct', true)->count();

            if ($correct === 0) {
                throw ValidationException::withMessages([
                    'questions' => 'Soal nomor '.$question->position.' belum memiliki jawaban benar.',
                ]);
            }

            if ($question->type !== QuestionType::MultipleSelect && $correct > 1) {
                throw ValidationException::withMessages([
                    'questions' => 'Soal nomor '.$question->position.' memiliki lebih dari satu jawaban benar.',
                ]);
            }
        }
    }

    private function assertWindow(?string $startsAt, ?string $endsAt): void
    {
        if ($startsAt !== null && $endsAt !== null && $endsAt <= $startsAt) {
            throw ValidationException::withMessages([
                'ends_at' => 'Waktu selesai harus setelah waktu mulai.',
            ]);
        }
    }
}
