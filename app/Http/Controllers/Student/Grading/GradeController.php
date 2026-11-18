<?php

namespace App\Http\Controllers\Student\Grading;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\Grading\GradeCalculationService;
use App\Services\Learning\CourseAccessService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(
        private readonly CourseAccessService $access,
        private readonly GradeCalculationService $calculation,
    ) {}

    public function index(Request $request): View
    {
        $enrollments = $request->user()
            ->enrollments()
            ->with(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom'])
            ->get()
            ->sortBy(fn ($enrollment): string => (string) $enrollment->course?->title)
            ->values();

        return view('portal.student.grades.index', [
            'enrollments' => $enrollments,
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        abort_unless($this->access->isEnrolled($request->user(), $course), 403);

        return view('portal.student.grades.show', [
            'course' => $course,
            'breakdown' => $this->calculation->breakdown($course, $request->user()),
        ]);
    }
}
