<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\CourseStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the teacher dashboard with real teaching data.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $assignmentIds = TeacherAssignment::query()
            ->where('teacher_id', $user->getKey())
            ->pluck('id');

        $courseQuery = Course::query()->whereIn('teacher_assignment_id', $assignmentIds);

        $courses = Course::query()
            ->whereIn('teacher_assignment_id', $assignmentIds)
            ->with(['teacherAssignment.classroom', 'teacherAssignment.subject'])
            ->withCount(['chapters', 'enrollments'])
            ->latest()
            ->limit(6)
            ->get();

        $enrollments = Enrollment::query()
            ->whereIn('course_id', (clone $courseQuery)->pluck('id'))
            ->count();

        return view('portal.teacher.dashboard', [
            'user' => $user,
            'isHomeroom' => $user->hasRole(RoleName::HomeroomTeacher->value),
            'courses' => $courses,
            'stats' => [
                'classes' => $assignmentIds->count(),
                'publishedCourses' => (clone $courseQuery)->where('status', CourseStatus::Published->value)->count(),
                'draftCourses' => (clone $courseQuery)->where('status', CourseStatus::Draft->value)->count(),
                'students' => $enrollments,
            ],
        ]);
    }
}
