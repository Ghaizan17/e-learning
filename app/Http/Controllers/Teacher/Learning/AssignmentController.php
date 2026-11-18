<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreAssignmentRequest;
use App\Http\Requests\Learning\UpdateAssignmentRequest;
use App\Models\Assignment;
use App\Models\Course;
use App\Services\Learning\AssignmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentService $assignments) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Assignment::class);

        $assignments = Assignment::query()
            ->with(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom'])
            ->whereHas('course.teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->withCount('submissions')
            ->orderByDesc('due_at')
            ->paginate(12);

        return view('portal.teacher.assignments.index', ['assignments' => $assignments]);
    }

    public function create(Request $request): View
    {
        $this->authorize('viewAny', Assignment::class);

        $courses = Course::query()
            ->with('teacherAssignment.subject', 'teacherAssignment.classroom')
            ->whereHas('teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->orderBy('title')
            ->get();

        return view('portal.teacher.assignments.create', ['courses' => $courses]);
    }

    public function store(StoreAssignmentRequest $request): RedirectResponse
    {
        $course = Course::query()->findOrFail($request->validated('course_id'));

        $assignment = $this->assignments->create($course, $request->validated());

        return redirect()
            ->route('teacher.assignments.show', $assignment)
            ->with('status', 'Tugas dibuat sebagai draf.');
    }

    public function show(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom', 'attachments']);

        $latest = $assignment->submissions()
            ->latest()
            ->with(['student', 'grade', 'files'])
            ->orderBy('attempt_no')
            ->get()
            ->sortBy(fn ($submission) => $submission->student?->name)
            ->values();

        $enrolledCount = $assignment->course->enrollments()->where('status', 'active')->count();

        return view('portal.teacher.assignments.show', [
            'assignment' => $assignment,
            'submissions' => $latest,
            'enrolledCount' => $enrolledCount,
        ]);
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorize('update', $assignment);

        return view('portal.teacher.assignments.edit', ['assignment' => $assignment->load('course')]);
    }

    public function update(UpdateAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->assignments->update($assignment, $request->validated());

        return redirect()
            ->route('teacher.assignments.show', $assignment)
            ->with('status', 'Tugas diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $this->assignments->delete($assignment);

        return redirect()
            ->route('teacher.assignments.index')
            ->with('status', 'Tugas dihapus.');
    }

    public function publish(Assignment $assignment): RedirectResponse
    {
        $this->authorize('publish', $assignment);

        try {
            $this->assignments->publish($assignment);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Tugas diterbitkan dan siswa diberi notifikasi.');
    }

    public function unpublish(Assignment $assignment): RedirectResponse
    {
        $this->authorize('publish', $assignment);

        $this->assignments->unpublish($assignment);

        return back()->with('status', 'Tugas disembunyikan dari siswa.');
    }
}
