<?php

namespace App\Services\Learning;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class QuizAutoGradingService
{
    /**
     * Grade every auto-gradable answer of the attempt and return the pending manual count.
     *
     * @return array{pending: int, score: float}
     */
    public function grade(QuizAttempt $attempt): array
    {
        $questions = $attempt->quiz->questions()->with('options')->get();
        $answers = $attempt->answers()->with('selectedOptions')->get()->keyBy('question_id');
        $pending = 0;
        $score = 0.0;

        foreach ($questions as $question) {
            /** @var QuizAnswer|null $answer */
            $answer = $answers->get($question->getKey());

            if (! $question->type->isAutoGraded()) {
                if ($answer === null || $answer->score_awarded === null) {
                    $pending++;
                } else {
                    $score += (float) $answer->score_awarded;
                }

                continue;
            }

            if ($answer === null) {
                continue;
            }

            $isCorrect = $this->isCorrect($question, $answer);
            $awarded = $isCorrect ? (float) $question->score : 0.0;

            $answer->update([
                'is_correct' => $isCorrect,
                'score_awarded' => $awarded,
                'graded_at' => now(),
            ]);

            $score += $awarded;
        }

        return ['pending' => $pending, 'score' => round($score, 2)];
    }

    public function pendingCount(QuizAttempt $attempt): int
    {
        $essayIds = $attempt->quiz->questions()
            ->where('type', QuestionType::Essay)
            ->pluck('id');

        if ($essayIds->isEmpty()) {
            return 0;
        }

        $graded = $attempt->answers()
            ->whereIn('question_id', $essayIds)
            ->whereNotNull('score_awarded')
            ->count();

        return $essayIds->count() - $graded;
    }

    private function isCorrect(Question $question, QuizAnswer $answer): bool
    {
        /** @var Collection<int, int> $correctIds */
        $correctIds = $question->options()->where('is_correct', true)->pluck('id');

        if ($question->type === QuestionType::MultipleSelect) {
            $selected = collect($answer->selectedOptions->pluck('question_option_id'))
                ->map(fn ($id): int => (int) $id)
                ->sort()
                ->values()
                ->all();

            return $selected === $correctIds->map(fn ($id): int => (int) $id)->sort()->values()->all();
        }

        if ($question->type === QuestionType::ShortAnswer) {
            $given = Str::lower(trim((string) $answer->answer_text));

            return $question->options
                ->where('is_correct', true)
                ->contains(fn ($option): bool => Str::lower(trim((string) $option->option_text)) === $given);
        }

        return $correctIds->contains((int) $answer->question_option_id);
    }
}
