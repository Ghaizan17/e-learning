<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;

class QuestionPolicy
{
    public function view(User $user, Question $question): bool
    {
        return $user->can('view', $question->quiz);
    }

    public function update(User $user, Question $question): bool
    {
        return $user->can('update', $question->quiz);
    }

    public function delete(User $user, Question $question): bool
    {
        return $user->can('update', $question->quiz);
    }
}
