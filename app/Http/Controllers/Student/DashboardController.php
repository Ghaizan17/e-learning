<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Enums\LessonProgressStatus;
use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Learning\LessonProgressService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly LessonProgressService $progress) {}

    /**
     * Show the student dashboard with real enrollment and progress data.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $enrollments = Enrollment::query()
            ->where('user_id', $user->getKey())
            ->whereIn('status', [EnrollmentStatus::Active->value, EnrollmentStatus::Completed->value])
            ->whereHas('course', fn ($query) => $query->published())
            ->with(['course.teacherAssignment.subject', 'course.teacherAssignment.teacher'])
            ->latest('enrolled_at')
            ->get();

        $courses = $enrollments->map(fn (Enrollment $enrollment): array => [
            'course' => $enrollment->course,
            'percent' => $this->progress->percent($user, $enrollment->course),
            'status' => $enrollment->status,
        ]);

        $averageProgress = $courses->isEmpty() ? 0 : (int) round($courses->avg('percent'));

        return view('portal.student.dashboard', [
            'user' => $user,
            'courses' => $courses,
            'stats' => [
                'courses' => $courses->count(),
                'completedLessons' => LessonProgress::query()->where('user_id', $user->getKey())->completed()->count(),
                'inProgress' => LessonProgress::query()->where('user_id', $user->getKey())
                    ->where('status', LessonProgressStatus::InProgress->value)->count(),
                'completedCourses' => $enrollments->where('status', EnrollmentStatus::Completed)->count(),
                'averageProgress' => $averageProgress,
            ],
        ]);
    }
}
