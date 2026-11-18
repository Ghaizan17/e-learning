<?php

namespace App\Http\Controllers\Student\Learning;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\Learning\QuizAttemptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private readonly QuizAttemptService $attempts) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $quizzes = Quiz::query()
            ->published()
            ->whereHas('course', fn ($query) => $query
                ->where('status', 'published')
                ->whereHas('enrollments', fn ($enrollment) => $enrollment
                    ->where('user_id', $user->getKey())
                    ->whereIn('status', ['active', 'completed'])))
            ->with([
                'course.teacherAssignment.subject',
                'attempts' => fn ($query) => $query->where('user_id', $user->getKey())->orderByDesc('attempt_no'),
            ])
            ->withCount('questions')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('portal.student.quizzes.index', ['quizzes' => $quizzes]);
    }

    public function show(Quiz $quiz): View
    {
        $this->authorize('view', $quiz);

        $quiz->load(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom']);

        $user = request()->user();
        $active = $quiz->activeAttemptFor($user);
        $latest = $quiz->latestAttemptFor($user);
        $usedAttempts = $quiz->attempts()->where('user_id', $user->getKey())->count();

        return view('portal.student.quizzes.show', [
            'quiz' => $quiz,
            'activeAttempt' => $active,
            'latestAttempt' => $latest,
            'usedAttempts' => $usedAttempts,
            'canStart' => $quiz->isOpen() && $usedAttempts < $quiz->max_attempts,
        ]);
    }

    public function start(Quiz $quiz): RedirectResponse
    {
        $this->authorize('take', $quiz);

        try {
            $attempt = $this->attempts->start($quiz, request()->user());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return redirect()->route('student.quiz-attempts.show', $attempt);
    }
}
