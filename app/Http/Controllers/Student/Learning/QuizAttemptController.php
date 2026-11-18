<?php

namespace App\Http\Controllers\Student\Learning;

use App\Enums\QuizAttemptStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\SaveQuizAnswerRequest;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use App\Services\Learning\QuizAttemptService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    public function __construct(private readonly QuizAttemptService $attempts) {}

    public function show(Request $request, QuizAttempt $attempt): View|RedirectResponse
    {
        $this->authorize('view', $attempt);

        if ($attempt->status !== QuizAttemptStatus::InProgress) {
            return redirect()->route('student.quiz-attempts.result', $attempt);
        }

        if ($attempt->expires_at->isPast()) {
            $this->attempts->expire($attempt);

            return redirect()
                ->route('student.quiz-attempts.result', $attempt)
                ->with('status', 'Waktu pengerjaan telah habis.');
        }

        $user = $request->user();
        $questions = $this->attempts->orderedQuestions($attempt);
        $answers = $attempt->answers()->with('selectedOptions')->get()->keyBy('question_id');

        return view('portal.student.quizzes.attempt', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $questions,
            'answers' => $answers,
            'remainingSeconds' => $attempt->remainingSeconds(),
        ]);
    }

    public function saveAnswer(SaveQuizAnswerRequest $request, QuizAttempt $attempt): JsonResponse
    {
        $question = Question::query()->findOrFail($request->validated('question_id'));

        try {
            $answer = $this->attempts->saveAnswer($attempt, $question, $request->validated());
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'message' => collect($exception->errors())->flatten()->first(),
            ], 422);
        }

        return response()->json([
            'ok' => true,
            'question_id' => $answer->question_id,
            'saved_at' => now()->toIso8601String(),
            'remaining_seconds' => $attempt->fresh()->remainingSeconds(),
        ]);
    }

    public function submit(Request $request, QuizAttempt $attempt): RedirectResponse
    {
        $this->authorize('submit', $attempt);

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', function (string $attribute, mixed $value, Closure $fail): void {
                $optionIds = is_array($value) ? $value : [$value];

                foreach ($optionIds as $optionId) {
                    if (! is_numeric($optionId) || ! QuestionOption::query()->whereKey($optionId)->exists()) {
                        $fail('Pilihan jawaban tidak valid.');

                        return;
                    }
                }
            }],
            'answers_text' => ['nullable', 'array'],
            'answers_text.*' => ['nullable', 'string', 'max:10000'],
        ]);

        try {
            foreach ($attempt->quiz->questions()->with('options')->get() as $question) {
                $raw = $data['answers'][$question->getKey()] ?? null;
                $text = $data['answers_text'][$question->getKey()] ?? null;

                if ($raw === null && $text === null) {
                    continue;
                }

                $this->attempts->saveAnswer($attempt, $question, [
                    'question_option_id' => $question->type->allowsMultipleSelection() ? null : $raw,
                    'option_ids' => $question->type->allowsMultipleSelection() ? (array) $raw : [],
                    'answer_text' => $text,
                ]);
            }

            $this->attempts->submit($attempt);
        } catch (ValidationException $exception) {
            return redirect()
                ->route('student.quiz-attempts.result', $attempt)
                ->withErrors($exception->errors());
        }

        return redirect()
            ->route('student.quiz-attempts.result', $attempt)
            ->with('status', 'Jawaban berhasil dikumpulkan.');
    }

    public function result(Request $request, QuizAttempt $attempt): View|RedirectResponse
    {
        $this->authorize('view', $attempt);

        if ($attempt->status === QuizAttemptStatus::InProgress) {
            if ($attempt->expires_at->isPast()) {
                $this->attempts->expire($attempt);
            } else {
                return redirect()->route('student.quiz-attempts.show', $attempt);
            }
        }

        if (! $attempt->canRevealResult()) {
            return redirect()
                ->route('student.quizzes.show', $attempt->quiz)
                ->with('status', 'Hasil akan tersedia setelah kuis berakhir.');
        }

        $attempt->load([
            'quiz.questions.options',
            'answers.selectedOption',
            'answers.selectedOptions',
            'answers.question.options',
        ]);

        return view('portal.student.quizzes.result', [
            'attempt' => $attempt,
            'quiz' => $attempt->quiz,
            'questions' => $attempt->quiz->questions,
            'answers' => $attempt->answers->keyBy('question_id'),
        ]);
    }
}
