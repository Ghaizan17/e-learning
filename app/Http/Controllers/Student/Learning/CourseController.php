<?php

namespace App\Http\Controllers\Student\Learning;

use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\Learning\CourseAccessService;
use App\Services\Learning\EnrollmentService;
use App\Services\Learning\LessonProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly CourseAccessService $access,
        private readonly LessonProgressService $progress,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $enrollments = Enrollment::query()
            ->where('user_id', $user->getKey())
            ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Completed->value])
            ->whereHas('course', fn ($query) => $query->published())
            ->with(['course.teacherAssignment.subject', 'course.teacherAssignment.classroom'])
            ->latest('enrolled_at')
            ->get();

        $courses = $enrollments->map(fn (Enrollment $enrollment): array => [
            'enrollment' => $enrollment,
            'course' => $enrollment->course,
            'percent' => $this->progress->percent($user, $enrollment->course),
        ]);

        return view('portal.student.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $user = $request->user();

        if ($user->hasRole(RoleName::Student->value)
            && $course->isPublished()
            && ! $this->access->isEnrolled($user, $course)
            && $this->enrollments->isClassroomMember($user, $course)) {
            $this->enrollments->enroll($user, $course);
        }

        $this->authorize('view', $course);

        $course->load([
            'teacherAssignment.subject',
            'teacherAssignment.classroom',
            'teacherAssignment.teacher',
        ]);

        $chapters = $course->chapters()
            ->published()
            ->with(['lessons' => fn ($query) => $query->where('is_published', true)->with('attachments')])
            ->get();

        $progress = $this->progress->progressForCourse($user, $course);

        return view('portal.student.courses.show', [
            'course' => $course,
            'chapters' => $chapters,
            'progress' => $progress,
            'percent' => $this->progress->percent($user, $course),
            'completed' => $this->progress->completedCount($user, $course),
            'totalLessons' => $course->publishedLessons()->count(),
        ]);
    }
}
