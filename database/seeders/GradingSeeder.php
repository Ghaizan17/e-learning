<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Services\Grading\GradeCalculationService;
use App\Services\Grading\GradeSyncService;
use App\Services\Grading\GradingSchemeService;
use Illuminate\Database\Seeder;

class GradingSeeder extends Seeder
{
    public function run(): void
    {
        $schemes = app(GradingSchemeService::class);
        $sync = app(GradeSyncService::class);
        $calculation = app(GradeCalculationService::class);

        Course::query()->with(['assignments', 'quizzes'])->each(function (Course $course) use ($schemes, $sync, $calculation): void {
            $schemes->ensureDefaultScheme($course);
            $sync->syncCourse($course);
            $calculation->recalculateCourse($course);
        });

        $this->command?->info('Grading schemes and gradebook synced.');
    }
}
