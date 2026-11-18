<?php

namespace App\Policies;

use App\Models\AssignmentAttachment;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class AssignmentAttachmentPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function download(User $user, AssignmentAttachment $attachment): bool
    {
        $assignment = $attachment->assignment;

        if ($assignment === null || ! $user->can('courses.view')) {
            return false;
        }

        $course = $assignment->course;

        if ($course === null || ! $this->access->canView($user, $course)) {
            return false;
        }

        return $assignment->is_published || $this->access->canManage($user, $course);
    }
}
