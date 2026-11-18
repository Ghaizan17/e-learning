<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;

class CourseAccessService
{
    /**
     * Admins see everything, owners see their drafts, students need an active enrollment.
     */
    public function canView(User $user, Course $course): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($this->isOwner($user, $course)) {
            return true;
        }

        return $course->isPublished() && $this->isEnrolled($user, $course);
    }

    public function canManage(User $user, Course $course): bool
    {
        return $user->isAdmin() || $this->isOwner($user, $course);
    }

    /**
     * Lessons of unpublished chapters stay hidden even from enrolled students.
     */
    public function canAccessLesson(User $user, Lesson $lesson): bool
    {
        $course = $lesson->chapter?->course;

        if ($course === null) {
            return false;
        }

        if ($this->canManage($user, $course)) {
            return true;
        }

        return $course->isPublished()
            && $lesson->isVisibleToStudents()
            && $this->isEnrolled($user, $course);
    }

    public function isEnrolled(User $user, Course $course): bool
    {
        return $course->enrollments()->where('user_id', $user->getKey())->active()->exists();
    }

    private function isOwner(User $user, Course $course): bool
    {
        return $course->teacherAssignment()->where('teacher_id', $user->getKey())->exists();
    }
}
