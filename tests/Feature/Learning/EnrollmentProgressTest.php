<?php

use App\Enums\EnrollmentStatus;
use App\Enums\LessonProgressStatus;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use App\Services\Learning\CourseService;
use App\Services\Learning\EnrollmentService;
use App\Services\Learning\LessonProgressService;
use App\Services\Learning\LessonService;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 2);
    $this->fixture->publishAll();
    $this->enrollments = app(EnrollmentService::class);
    $this->progress = app(LessonProgressService::class);
});

it('syncs classroom members idempotently', function (): void {
    expect(Enrollment::query()->count())->toBe(2);

    $created = $this->enrollments->syncCourse($this->fixture->course);

    expect($created)->toBe(0)
        ->and(Enrollment::query()->count())->toBe(2);
});

it('does not enroll students who left the classroom', function (): void {
    $leaver = User::factory()->student()->create();
    app(ClassroomService::class)->enroll($leaver, $this->fixture->classroom);
    $membership = app(ClassroomService::class)->activeMembershipInClassroom($leaver, $this->fixture->classroom);
    app(ClassroomService::class)->remove($membership);

    $this->enrollments->syncCourse($this->fixture->course);

    expect(Enrollment::query()->where('user_id', $leaver->getKey())->exists())->toBeFalse();
});

it('reactivates a dropped enrollment instead of duplicating it', function (): void {
    $student = $this->fixture->students[0];
    $enrollment = Enrollment::query()->where('user_id', $student->getKey())->firstOrFail();

    $this->enrollments->drop($enrollment);
    $this->enrollments->enroll($student, $this->fixture->course);

    expect(Enrollment::query()->where('user_id', $student->getKey())->count())->toBe(1)
        ->and($enrollment->fresh()->status)->toBe(EnrollmentStatus::Active);
});

it('tracks progress per student and completes the enrollment at 100 percent', function (): void {
    $student = $this->fixture->students[0];

    $started = $this->progress->start($student, $this->fixture->lesson);

    expect($started->status)->toBe(LessonProgressStatus::InProgress)
        ->and($this->progress->percent($student, $this->fixture->course))->toBe(0);

    $completed = $this->progress->complete($student, $this->fixture->lesson);
    $enrollment = Enrollment::query()->where('user_id', $student->getKey())->firstOrFail();

    expect($completed->status)->toBe(LessonProgressStatus::Completed)
        ->and($completed->completed_at)->not->toBeNull()
        ->and($this->progress->percent($student, $this->fixture->course))->toBe(100)
        ->and($this->progress->completedCount($student, $this->fixture->course))->toBe(1)
        ->and($enrollment->status)->toBe(EnrollmentStatus::Completed)
        ->and($enrollment->completed_at)->not->toBeNull();
});

it('ignores unpublished lessons in the progress percentage', function (): void {
    $second = app(LessonService::class)->create($this->fixture->chapter, ['title' => 'Materi Draf']);

    expect($second->is_published)->toBeFalse()
        ->and($this->progress->percent($this->fixture->students[0], $this->fixture->course))->toBe(0);

    $this->progress->complete($this->fixture->students[0], $this->fixture->lesson);

    expect($this->progress->percent($this->fixture->students[0], $this->fixture->course))->toBe(100);
});

it('keeps progress isolated between students', function (): void {
    $first = $this->fixture->students[0];
    $second = $this->fixture->students[1];

    $this->progress->complete($first, $this->fixture->lesson);

    expect(LessonProgress::query()->where('user_id', $first->getKey())->completed()->count())->toBe(1)
        ->and(LessonProgress::query()->where('user_id', $second->getKey())->count())->toBe(0)
        ->and($this->progress->percent($second, $this->fixture->course))->toBe(0);
});

it('returns zero percent when no lesson is published yet', function (): void {
    $emptyCourse = app(CourseService::class)
        ->create($this->fixture->assignment, ['title' => 'Kursus Kosong']);

    expect($this->progress->percent($this->fixture->students[0], $emptyCourse))->toBe(0);
});
