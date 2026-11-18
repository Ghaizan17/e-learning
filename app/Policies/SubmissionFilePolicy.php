<?php

namespace App\Policies;

use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class SubmissionFilePolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function download(User $user, SubmissionFile $file): bool
    {
        $submission = $file->submission;

        if ($submission === null) {
            return false;
        }

        if ($submission->user_id === $user->getKey()) {
            return true;
        }

        return $user->can('submissions.view')
            && $this->access->canManage($user, $submission->assignment->course);
    }
}
