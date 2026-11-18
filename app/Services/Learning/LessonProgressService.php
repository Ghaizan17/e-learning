<?php

namespace App\Services\Learning;

use App\Enums\LessonProgressStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LessonProgressService
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
    ) {}

    /**
     * Record that a student opened a lesson.
     */
    public function start(User $user, Lesson $lesson): LessonProgress
    {
        return DB::transaction(function () use ($user, $lesson): LessonProgress {
            $progress = LessonProgress::query()->firstOrNew([
                'user_id' => $user->getKey(),
                'lesson_id' => $lesson->getKey(),
            ]);

            if (! $progress->exists) {
                $progress->status = LessonProgressStatus::InProgress;
                $progress->started_at = now();
            }

            if ($progress->status === LessonProgressStatus::NotStarted) {
                $progress->status = LessonProgressStatus::InProgress;
            }

            $progress->last_viewed_at = now();
            $progress->save();

            return $progress;
        });
    }

    /**
     * Mark a lesson as completed and finish the enrollment at 100%.
     */
    public function complete(User $user, Lesson $lesson): LessonProgress
    {
        return DB::transaction(function () use ($user, $lesson): LessonProgress {
            $progress = LessonProgress::query()->firstOrNew([
                'user_id' => $user->getKey(),
                'lesson_id' => $lesson->getKey(),
            ]);

            $progress->fill([
                'status' => LessonProgressStatus::Completed,
                'started_at' => $progress->started_at ?? now(),
                'completed_at' => now(),
                'last_viewed_at' => now(),
            ])->save();

            $course = $lesson->chapter->course;

            if ($course !== null) {
                $this->finishEnrollmentWhenDone($user, $course);
            }

            return $progress;
        });
    }

    public function percent(User $user, Course $course): int
    {
        $total = $course->publishedLessons()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->completedCount($user, $course);

        return (int) round(($completed / $total) * 100);
    }

    public function completedCount(User $user, Course $course): int
    {
        return LessonProgress::query()
            ->where('user_id', $user->getKey())
            ->completed()
            ->whereIn('lesson_id', $course->publishedLessons()->pluck('lessons.id'))
            ->count();
    }

    /**
     * @return array<int, LessonProgress>
     */
    public function progressForCourse(User $user, Course $course): array
    {
        return LessonProgress::query()
            ->where('user_id', $user->getKey())
            ->whereIn('lesson_id', $course->publishedLessons()->pluck('lessons.id'))
            ->get()
            ->keyBy('lesson_id')
            ->all();
    }

    /**
     * Completing the last lesson closes the enrollment.
     */
    private function finishEnrollmentWhenDone(User $user, Course $course): void
    {
        if ($this->percent($user, $course) < 100) {
            return;
        }

        $enrollment = Enrollment::query()
            ->where('course_id', $course->getKey())
            ->where('user_id', $user->getKey())
            ->active()
            ->first();

        if ($enrollment !== null) {
            $this->enrollments->markCompleted($enrollment);
        }
    }
}
