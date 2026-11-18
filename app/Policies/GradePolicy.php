<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\User;
use App\Services\Learning\CourseAccessService;

class GradePolicy
{
    public function __construct(private readonly CourseAccessService $access) {}

    public function viewAny(User $user): bool
    {
        return $user->can('grades.view');
    }

    public function view(User $user, Grade $grade): bool
    {
        $course = $grade->course;

        if ($course === null) {
            return false;
        }

        if ($grade->user_id === $user->getKey()) {
            return $grade->is_published;
        }

        return $this->access->canManage($user, $course);
    }

    public function update(User $user, Grade $grade): bool
    {
        return $user->can('grades.manage')
            && $grade->course !== null
            && $this->access->canManage($user, $grade->course);
    }

    public function publish(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }

    public function viewHistory(User $user, Grade $grade): bool
    {
        return $this->update($user, $grade);
    }

    public function viewHistoryEntry(User $user, GradeHistory $history): bool
    {
        return $this->update($user, $history->grade);
    }
}
