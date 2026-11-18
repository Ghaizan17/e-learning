<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreQuizRequest;
use App\Http\Requests\Learning\UpdateQuizRequest;
use App\Models\Course;
use App\Models\Quiz;
use App\Services\Learning\QuizService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private readonly QuizService $quizzes) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quiz::class);

        $quizzes = Quiz::query()
            ->with(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom'])
            ->whereHas('course.teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->withCount(['questions', 'attempts'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('portal.teacher.quizzes.index', ['quizzes' => $quizzes]);
    }

    public function create(Request $request): View
    {
        $this->authorize('viewAny', Quiz::class);

        $courses = Course::query()
            ->with('teacherAssignment.subject', 'teacherAssignment.classroom')
            ->whereHas('teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->orderBy('title')
            ->get();

        return view('portal.teacher.quizzes.create', ['courses' => $courses]);
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $course = Course::query()->findOrFail($request->validated('course_id'));

        $quiz = $this->quizzes->create($course, $request->validated());

        return redirect()
            ->route('teacher.quizzes.show', $quiz)
            ->with('status', 'Kuis dibuat sebagai draf. Tambahkan soal lalu terbitkan.');
    }

    public function show(Quiz $quiz): View
    {
        $this->authorize('view', $quiz);

        $quiz->load([
            'course.teacherAssignment.subject',
            'course.teacherAssignment.classroom',
            'questions.options',
        ]);

        $attempts = $quiz->attempts()
            ->with('student')
            ->orderByDesc('started_at')
            ->get();

        return view('portal.teacher.quizzes.show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
        ]);
    }

    public function edit(Quiz $quiz): View
    {
        $this->authorize('update', $quiz);

        return view('portal.teacher.quizzes.edit', [
            'quiz' => $quiz->load('course.teacherAssignment.subject'),
        ]);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        try {
            $this->quizzes->update($quiz, $request->validated());
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors())->withInput();
        }

        return redirect()
            ->route('teacher.quizzes.show', $quiz)
            ->with('status', 'Kuis diperbarui.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        $this->quizzes->delete($quiz);

        return redirect()
            ->route('teacher.quizzes.index')
            ->with('status', 'Kuis dihapus.');
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $this->authorize('publish', $quiz);

        try {
            $this->quizzes->publish($quiz);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Kuis diterbitkan.');
    }

    public function unpublish(Quiz $quiz): RedirectResponse
    {
        $this->authorize('publish', $quiz);

        $this->quizzes->unpublish($quiz);

        return back()->with('status', 'Kuis disembunyikan dari siswa.');
    }
}
