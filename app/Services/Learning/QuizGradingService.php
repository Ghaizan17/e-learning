<?php

namespace App\Services\Learning;

use App\Enums\QuestionType;
use App\Enums\QuizAttemptStatus;
use App\Models\QuizAnswer;
use App\Models\User;
use App\Notifications\QuizAttemptGradedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizGradingService
{
    public function __construct(private readonly QuizAutoGradingService $auto) {}

    /**
     * Manually grade an essay answer and recompute the attempt total.
     */
    public function gradeAnswer(QuizAnswer $answer, User $grader, float $score): QuizAnswer
    {
        $question = $answer->question;

        if ($question->type !== QuestionType::Essay) {
            throw ValidationException::withMessages([
                'score' => 'Hanya jawaban esai yang dinilai manual.',
            ]);
        }

        if ($score < 0 || $score > (float) $question->score) {
            throw ValidationException::withMessages([
                'score' => 'Skor harus berada di antara 0 dan '.$question->score.'.',
            ]);
        }

        return DB::transaction(function () use ($answer, $grader, $score, $question): QuizAnswer {
            $answer->update([
                'score_awarded' => $score,
                'is_correct' => $score >= (float) $question->score,
                'graded_by' => $grader->getKey(),
                'graded_at' => now(),
            ]);

            $attempt = $answer->attempt;
            $pending = $this->auto->pendingCount($attempt);

            $total = (float) $attempt->answers()->sum('score_awarded');

            $attempt->update([
                'score' => round($total, 2),
                'status' => $pending === 0
                    ? QuizAttemptStatus::Graded
                    : QuizAttemptStatus::Submitted,
            ]);

            if ($pending === 0) {
                $attempt->student?->notify(new QuizAttemptGradedNotification($attempt->refresh()->load('quiz')));
            }

            return $answer->refresh();
        });
    }
}
