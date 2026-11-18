<?php

namespace App\Jobs;

use App\Services\Learning\QuizAttemptService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireQuizAttempts implements ShouldQueue
{
    use Queueable;

    /**
     * Close attempts whose server-side deadline has passed.
     */
    public function handle(QuizAttemptService $attempts): void
    {
        $attempts->expireOverdue();
    }
}
