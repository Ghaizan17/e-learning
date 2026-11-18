<?php

namespace App\Services\Learning;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptService
{
    public function __construct(
        private readonly CourseAccessService $access,
        private readonly QuizAutoGradingService $grading,
    ) {}

    /**
     * Start a new attempt or resume an unexpired one.
     */
    public function start(Quiz $quiz, User $student): QuizAttempt
    {
        if (! $quiz->is_published) {
            throw ValidationException::withMessages(['quiz' => 'Kuis belum diterbitkan.']);
        }

        if (! $this->access->isEnrolled($student, $quiz->course)) {
            throw ValidationException::withMessages(['quiz' => 'Anda tidak terdaftar pada kursus ini.']);
        }

        if (! $quiz->hasStarted()) {
            throw ValidationException::withMessages(['quiz' => 'Kuis belum dibuka.']);
        }

        if ($quiz->hasEnded()) {
            throw ValidationException::withMessages(['quiz' => 'Kuis sudah ditutup.']);
        }

        return DB::transaction(function () use ($quiz, $student): QuizAttempt {
            Quiz::query()->whereKey($quiz->getKey())->lockForUpdate()->first();

            $active = $quiz->activeAttemptFor($student);

            if ($active !== null && ! $active->isExpired()) {
                return $active;
            }

            if ($active !== null) {
                $this->expireAttempt($active);
            }

            $used = $quiz->attempts()->where('user_id', $student->getKey())->count();

            if ($used >= $quiz->max_attempts) {
                throw ValidationException::withMessages([
                    'quiz' => 'Batas percobaan untuk kuis ini sudah tercapai.',
                ]);
            }

            $now = now();
            $expiresAt = $now->copy()->addMinutes($quiz->duration_minutes);

            if ($quiz->ends_at !== null && $quiz->ends_at->lessThan($expiresAt)) {
                $expiresAt = $quiz->ends_at->copy();
            }

            return $quiz->attempts()->create([
                'user_id' => $student->getKey(),
                'attempt_no' => $used + 1,
                'started_at' => $now,
                'expires_at' => $expiresAt,
                'status' => QuizAttemptStatus::InProgress,
            ]);
        });
    }

    /**
     * Persist a single answer while the attempt is still open.
     *
     * @param  array{question_option_id?: int|null, option_ids?: array<int, int>, answer_text?: string|null}  $data
     */
    public function saveAnswer(QuizAttempt $attempt, Question $question, array $data): QuizAnswer
    {
        if ($question->quiz_id !== $attempt->quiz_id) {
            throw ValidationException::withMessages(['answer' => 'Soal bukan bagian dari kuis ini.']);
        }

        $remaining = $attempt->remainingSeconds();

        if ($attempt->status !== QuizAttemptStatus::InProgress || $remaining <= 0) {
            if ($attempt->status === QuizAttemptStatus::InProgress) {
                $this->expire($attempt);
            }

            throw ValidationException::withMessages(['answer' => 'Waktu pengerjaan telah habis.']);
        }

        if (! empty($data['question_option_id'])
            && ! $question->options()->whereKey($data['question_option_id'])->exists()) {
            throw ValidationException::withMessages(['answer' => 'Pilihan jawaban tidak valid untuk soal ini.']);
        }

        return DB::transaction(function () use ($attempt, $question, $data): QuizAnswer {
            $answer = $attempt->answers()->updateOrCreate(
                ['question_id' => $question->getKey()],
                [
                    'question_option_id' => $data['question_option_id'] ?? null,
                    'answer_text' => $data['answer_text'] ?? null,
                    'is_correct' => null,
                    'score_awarded' => null,
                ],
            );

            if ($question->type === QuestionType::MultipleSelect) {
                $optionIds = $data['option_ids'] ?? [];

                $valid = $question->options()->whereIn('id', $optionIds)->pluck('id')->all();

                $answer->selectedOptions()->delete();
                $answer->selectedOptions()->createMany(
                    collect($valid)->map(fn ($id): array => ['question_option_id' => $id])->all(),
                );
            }

            return $answer->refresh();
        });
    }

    /**
     * Submit the attempt, auto-grade it, and lock out further submissions.
     */
    public function submit(QuizAttempt $attempt): QuizAttempt
    {
        $expired = false;

        $attempt = DB::transaction(function () use ($attempt, &$expired): QuizAttempt {
            /** @var QuizAttempt $locked */
            $locked = QuizAttempt::query()->whereKey($attempt->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== QuizAttemptStatus::InProgress) {
                throw ValidationException::withMessages([
                    'quiz' => 'Jawaban sudah dikumpulkan sebelumnya.',
                ]);
            }

            if ($locked->expires_at->isPast()) {
                $this->expireAttempt($locked);
                $expired = true;

                return $locked;
            }

            $result = $this->grading->grade($locked);

            $locked->update([
                'status' => $result['pending'] === 0 ? QuizAttemptStatus::Graded : QuizAttemptStatus::Submitted,
                'score' => $result['score'],
                'submitted_at' => now(),
            ]);

            return $locked->refresh();
        });

        if ($expired) {
            throw ValidationException::withMessages([
                'quiz' => 'Waktu ujian telah habis. Jawaban tidak dapat dikirim.',
            ]);
        }

        return $attempt;
    }

    public function expire(QuizAttempt $attempt): QuizAttempt
    {
        if ($attempt->status !== QuizAttemptStatus::InProgress) {
            return $attempt;
        }

        DB::transaction(fn () => $this->expireAttempt($attempt));

        return $attempt->refresh();
    }

    /**
     * Expire all attempts whose server-side deadline has passed.
     */
    public function expireOverdue(): int
    {
        $count = 0;

        QuizAttempt::query()
            ->inProgress()
            ->where('expires_at', '<=', now())
            ->chunkById(100, function ($attempts) use (&$count): void {
                foreach ($attempts as $attempt) {
                    $this->expire($attempt);
                    $count++;
                }
            });

        return $count;
    }

    private function expireAttempt(QuizAttempt $attempt): void
    {
        $result = $this->grading->grade($attempt);

        $attempt->update([
            'status' => QuizAttemptStatus::Expired,
            'score' => $result['score'],
            'submitted_at' => $attempt->expires_at ?? now(),
        ]);
    }

    /**
     * @return array<int, Question>
     */
    public function orderedQuestions(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $questions = $quiz->questions()->with('options')->get();

        if ($quiz->shuffle_questions) {
            $questions = $questions->sortBy(
                fn (Question $question): int => crc32($attempt->getKey().'-'.$question->getKey()),
            )->values();
        }

        if ($quiz->shuffle_options) {
            $questions->each(function (Question $question) use ($attempt): void {
                $question->setRelation(
                    'options',
                    $question->options->sortBy(
                        fn ($option): int => crc32($attempt->getKey().'-'.$question->getKey().'-'.$option->getKey()),
                    )->values(),
                );
            });
        }

        return $questions->all();
    }
}
