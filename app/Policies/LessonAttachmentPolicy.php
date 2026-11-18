<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\LessonAttachment;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class LessonAttachmentPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function download(User $user, LessonAttachment $attachment): bool
    {
        $lesson = $attachment->lesson;

        return $lesson !== null
            && $user->can('courses.view')
            && $this->access->canAccessLesson($user, $lesson);
    }

    public function create(User $user, Lesson $lesson): bool
    {
        return $user->can('lessons.manage')
            && $this->access->canManage($user, $lesson->chapter->course);
    }

    public function delete(User $user, LessonAttachment $attachment): bool
    {
        return $this->create($user, $attachment->lesson);
    }
}
