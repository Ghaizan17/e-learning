<?php

namespace App\Http\Controllers\Student\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\SubmitAssignmentRequest;
use App\Models\Assignment;
use App\Services\Learning\AssignmentSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(private readonly AssignmentSubmissionService $submissions) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $assignments = Assignment::query()
            ->published()
            ->whereHas('course', fn ($query) => $query
                ->where('status', 'published')
                ->whereHas('enrollments', fn ($enrollment) => $enrollment
                    ->where('user_id', $user->getKey())
                    ->whereIn('status', ['active', 'completed'])))
            ->with([
                'course.teacherAssignment.subject',
                'submissions' => fn ($query) => $query
                    ->where('user_id', $user->getKey())
                    ->where('is_latest', true)
                    ->with(['grade', 'files']),
            ])
            ->orderBy('due_at')
            ->paginate(12);

        return view('portal.student.assignments.index', ['assignments' => $assignments]);
    }

    public function show(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom', 'attachments']);

        $latest = $this->submissions->latestFor($assignment, request()->user());

        return view('portal.student.assignments.show', [
            'assignment' => $assignment,
            'submission' => $latest,
            'canSubmit' => $this->submissions->canSubmit($assignment, request()->user()),
        ]);
    }

    public function submit(SubmitAssignmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->submissions->submit(
            $assignment,
            $request->user(),
            ['content' => $request->validated('content')],
            $request->file('files', []),
        );

        return redirect()
            ->route('student.assignments.show', $assignment)
            ->with('status', 'Tugas berhasil dikumpulkan.');
    }
}
