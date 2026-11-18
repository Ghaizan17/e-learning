<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\TeacherAssignmentService;

beforeEach(function (): void {
    $this->year = AcademicYear::factory()->active()->create();
    $this->semester = Semester::factory()->forYear($this->year)->odd()->create();
    $this->classroom = Classroom::factory()->forYear($this->year)->create();
    $this->otherClassroom = Classroom::factory()->forYear($this->year)->create();
    $this->subject = Subject::factory()->create();
    $this->teacher = User::factory()->teacher()->create();
    $this->homeroom = User::factory()->homeroomTeacher()->create();
    $this->admin = User::factory()->schoolAdmin()->create();
    $this->student = User::factory()->student()->create();

    $this->classroom->update(['homeroom_teacher_id' => $this->homeroom->getKey()]);

    app(TeacherAssignmentService::class)
        ->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);
});

it('lets admins view any classroom', function (): void {
    expect($this->admin->can('viewAny', Classroom::class))->toBeTrue()
        ->and($this->admin->can('view', $this->classroom))->toBeTrue();
});

it('lets a teacher view only their assigned classroom', function (): void {
    expect($this->teacher->can('view', $this->classroom))->toBeTrue()
        ->and($this->teacher->can('view', $this->otherClassroom))->toBeFalse();
});

it('lets a homeroom teacher view their own classroom', function (): void {
    expect($this->homeroom->can('view', $this->classroom))->toBeTrue()
        ->and($this->homeroom->can('view', $this->otherClassroom))->toBeFalse();
});

it('forbids students from viewing classrooms', function (): void {
    expect($this->student->can('view', $this->classroom))->toBeFalse()
        ->and($this->student->can('viewAny', Classroom::class))->toBeFalse();
});

it('forbids teachers from managing classrooms', function (): void {
    expect($this->teacher->can('update', $this->classroom))->toBeFalse()
        ->and($this->teacher->can('create', Classroom::class))->toBeFalse();
});

it('allows academic years to be viewed but not managed by teachers', function (): void {
    $year = AcademicYear::factory()->create();

    expect($this->teacher->can('viewAny', AcademicYear::class))->toBeTrue()
        ->and($this->teacher->can('create', AcademicYear::class))->toBeFalse()
        ->and($this->admin->can('create', AcademicYear::class))->toBeTrue();
});

it('hides other teachers assignments from a teacher', function (): void {
    $otherTeacher = User::factory()->teacher()->create();
    $assignment = $this->teacher->teachingAssignments()->first();

    expect($this->teacher->can('view', $assignment))->toBeTrue()
        ->and($otherTeacher->can('view', $assignment))->toBeFalse();
});

it('gives super admins every academic ability', function (): void {
    $superAdmin = User::factory()->superAdmin()->create();

    expect($superAdmin->can('create', AcademicYear::class))->toBeTrue()
        ->and($superAdmin->can('update', $this->classroom))->toBeTrue()
        ->and($superAdmin->can('view', $this->classroom))->toBeTrue();
});
