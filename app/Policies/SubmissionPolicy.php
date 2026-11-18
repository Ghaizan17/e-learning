<?php

namespace App\Policies;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class SubmissionPolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function view(User $user, Submission $submission): bool
    {
        if ($submission->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('submissions.view')
            && $this->access->canManage($user, $submission->assignment->course);
    }

    public function create(User $user, Assignment $assignment): bool
    {
        return $assignment->is_published
            && $user->can('courses.view')
            && $this->access->isEnrolled($user, $assignment->course);
    }

    public function grade(User $user, Submission $submission): bool
    {
        return $user->can('submissions.grade')
            && $this->access->canManage($user, $submission->assignment->course);
    }

    public function downloadFiles(User $user, Submission $submission): bool
    {
        return $this->view($user, $submission);
    }
}
