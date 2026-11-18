<?php

use App\Jobs\ExpireQuizAttempts;
use App\Jobs\SendAssignmentDeadlineReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new SendAssignmentDeadlineReminders)->hourly();
Schedule::job(new ExpireQuizAttempts)->everyFiveMinutes();
