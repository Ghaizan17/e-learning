<?php

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Academic\TeacherAssignmentService;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->year = AcademicYear::factory()->active()->create();
    $this->semester = Semester::factory()->forYear($this->year)->odd()->active()->create();
    $this->classroom = Classroom::factory()->forYear($this->year)->create();
    $this->subject = Subject::factory()->create();
    $this->teacher = User::factory()->teacher()->create();
});

it('creates a teaching assignment', function (): void {
    $assignment = app(TeacherAssignmentService::class)
        ->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);

    expect($assignment->teacher_id)->toBe($this->teacher->getKey())
        ->and($assignment->classroom_id)->toBe($this->classroom->getKey())
        ->and($assignment->semester_id)->toBe($this->semester->getKey());
});

it('refuses a duplicate assignment', function (): void {
    $service = app(TeacherAssignmentService::class);

    $service->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);
    $service->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);
})->throws(ValidationException::class);

it('refuses to assign a non-teacher account', function (): void {
    $student = User::factory()->student()->create();

    app(TeacherAssignmentService::class)
        ->assign($student, $this->classroom, $this->subject, $this->year, $this->semester);
})->throws(ValidationException::class);

it('refuses a semester belonging to another academic year', function (): void {
    $otherYear = AcademicYear::factory()->create();
    $otherSemester = Semester::factory()->forYear($otherYear)->odd()->create();

    app(TeacherAssignmentService::class)
        ->assign($this->teacher, $this->classroom, $this->subject, $this->year, $otherSemester);
})->throws(ValidationException::class);

it('refuses a classroom from another academic year', function (): void {
    $otherYear = AcademicYear::factory()->create();
    $otherClassroom = Classroom::factory()->forYear($otherYear)->create();

    app(TeacherAssignmentService::class)
        ->assign($this->teacher, $otherClassroom, $this->subject, $this->year, $this->semester);
})->throws(ValidationException::class);

it('updates an assignment and blocks duplicates', function (): void {
    $service = app(TeacherAssignmentService::class);

    $first = $service->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);
    $otherSubject = Subject::factory()->create();
    $second = $service->assign($this->teacher, $this->classroom, $otherSubject, $this->year, $this->semester);

    $service->update($second, [
        'teacher_id' => $this->teacher->getKey(),
        'classroom_id' => $this->classroom->getKey(),
        'subject_id' => $first->subject_id,
        'academic_year_id' => $this->year->getKey(),
        'semester_id' => $this->semester->getKey(),
    ]);
})->throws(ValidationException::class);

it('finds the assignments of a teacher', function (): void {
    app(TeacherAssignmentService::class)
        ->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);

    expect($this->teacher->teachingAssignments()->count())->toBe(1)
        ->and($this->teacher->teachesIn($this->classroom))->toBeTrue()
        ->and(TeacherAssignment::query()->count())->toBe(1);
});
