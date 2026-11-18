<?php

use App\Enums\CourseStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\Learning\ChapterService;
use App\Services\Learning\CourseService;
use App\Services\Learning\LessonService;
use Illuminate\Validation\ValidationException;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 3);
    $this->courses = app(CourseService::class);
});

it('creates a draft course with a unique slug', function (): void {
    expect($this->fixture->course->status)->toBe(CourseStatus::Draft)
        ->and($this->fixture->course->slug)->toBe('pemrograman-web-dasar')
        ->and($this->fixture->course->published_at)->toBeNull();

    $duplicate = $this->courses->create($this->fixture->assignment, ['title' => 'Pemrograman Web Dasar']);

    expect($duplicate->slug)->toBe('pemrograman-web-dasar-2');
});

it('refuses to publish a course without a published lesson', function (): void {
    $this->courses->publish($this->fixture->course);
})->throws(ValidationException::class);

it('publishes a course and enrolls the classroom students', function (): void {
    $this->fixture->publishAll();

    $course = $this->fixture->course->fresh();

    expect($course->status)->toBe(CourseStatus::Published)
        ->and($course->published_at)->not->toBeNull()
        ->and(Enrollment::query()->where('course_id', $course->getKey())->count())->toBe(3)
        ->and($course->enrollments()->where('user_id', $this->fixture->students[0]->getKey())->active()->exists())->toBeTrue();
});

it('archives a published course and clears the publication date', function (): void {
    $this->fixture->publishAll();

    $this->courses->archive($this->fixture->course);

    expect($this->fixture->course->fresh()->status)->toBe(CourseStatus::Archived)
        ->and($this->fixture->course->fresh()->published_at)->toBeNull();
});

it('refuses to publish a lesson inside an unpublished chapter', function (): void {
    app(LessonService::class)->publish($this->fixture->lesson);
})->throws(ValidationException::class);

it('scopes published courses', function (): void {
    $this->fixture->publishAll();

    expect(Course::query()->published()->count())->toBe(1)
        ->and(Course::query()->where('slug', 'pemrograman-web-dasar')->exists())->toBeTrue();
});

it('keeps chapter and lesson positions increasing', function (): void {
    $chapterService = app(ChapterService::class);
    $second = $chapterService->create($this->fixture->course, ['title' => 'Dasar CSS']);
    $lessonService = app(LessonService::class);
    $secondLesson = $lessonService->create($second, ['title' => 'Selector CSS']);

    expect($this->fixture->chapter->position)->toBe(1)
        ->and($second->position)->toBe(2)
        ->and($this->fixture->lesson->position)->toBe(1)
        ->and($secondLesson->position)->toBe(1);
});
