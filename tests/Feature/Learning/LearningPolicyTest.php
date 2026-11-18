<?php

use App\Models\LessonAttachment;
use App\Models\User;
use App\Services\Learning\ChapterService;
use App\Services\Learning\EnrollmentService;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 2);
    $this->student = $this->fixture->students[0];
    $this->outsider = User::factory()->student()->create();
    $this->otherTeacher = User::factory()->teacher()->create();
    $this->admin = User::factory()->schoolAdmin()->create();
});

it('lets the owning teacher manage a draft course', function (): void {
    expect($this->fixture->teacher->can('view', $this->fixture->course))->toBeTrue()
        ->and($this->fixture->teacher->can('update', $this->fixture->course))->toBeTrue()
        ->and($this->fixture->teacher->can('publish', $this->fixture->course))->toBeTrue()
        ->and($this->otherTeacher->can('view', $this->fixture->course))->toBeFalse()
        ->and($this->otherTeacher->can('update', $this->fixture->course))->toBeFalse();
});

it('hides a draft course from enrolled students until it is published', function (): void {
    app(EnrollmentService::class)->enroll($this->student, $this->fixture->course);

    expect($this->student->can('view', $this->fixture->course))->toBeFalse();

    $this->fixture->publishAll();

    expect($this->student->can('view', $this->fixture->course))->toBeTrue();
});

it('forbids a published course to students outside the classroom', function (): void {
    $this->fixture->publishAll();

    expect($this->outsider->can('view', $this->fixture->course))->toBeFalse();
});

it('lets admins view and manage every course', function (): void {
    expect($this->admin->can('view', $this->fixture->course))->toBeTrue()
        ->and($this->admin->can('update', $this->fixture->course))->toBeTrue();
});

it('hides lessons of an unpublished chapter from students', function (): void {
    $this->fixture->publishAll();

    expect($this->student->can('view', $this->fixture->lesson))->toBeTrue();

    app(ChapterService::class)->unpublish($this->fixture->chapter);

    expect($this->student->can('view', $this->fixture->lesson->fresh()))->toBeFalse();
});

it('lets only the owning teacher manage chapters and lessons', function (): void {
    expect($this->fixture->teacher->can('update', $this->fixture->chapter))->toBeTrue()
        ->and($this->fixture->teacher->can('update', $this->fixture->lesson))->toBeTrue()
        ->and($this->otherTeacher->can('update', $this->fixture->chapter))->toBeFalse()
        ->and($this->otherTeacher->can('update', $this->fixture->lesson))->toBeFalse()
        ->and($this->student->can('update', $this->fixture->lesson))->toBeFalse();
});

it('guards private attachment downloads with enrollment', function (): void {
    $attachment = LessonAttachment::factory()->create(['lesson_id' => $this->fixture->lesson->getKey()]);

    $this->fixture->publishAll();

    expect($this->student->can('download', $attachment))->toBeTrue()
        ->and($this->outsider->can('download', $attachment))->toBeFalse()
        ->and($this->otherTeacher->can('download', $attachment))->toBeFalse()
        ->and($this->fixture->teacher->can('download', $attachment))->toBeTrue();
});
