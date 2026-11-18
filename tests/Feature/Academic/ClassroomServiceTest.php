<?php

use App\Enums\ClassroomMemberStatus;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use Illuminate\Validation\ValidationException;

beforeEach(function (): void {
    $this->year = AcademicYear::factory()->active()->create();
    $this->classroom = Classroom::factory()->forYear($this->year)->create(['name' => 'XI PPLG A', 'capacity' => 2]);
    $this->otherClassroom = Classroom::factory()->forYear($this->year)->create(['name' => 'XI PPLG B']);
    $this->student = User::factory()->student()->create();
});

it('enrolls a student as an active member', function (): void {
    $membership = app(ClassroomService::class)->enroll($this->student, $this->classroom);

    expect($membership->status)->toBe(ClassroomMemberStatus::Active)
        ->and($membership->left_at)->toBeNull()
        ->and($membership->user_id)->toBe($this->student->getKey());
});

it('treats a repeated enrollment as idempotent', function (): void {
    $service = app(ClassroomService::class);

    $first = $service->enroll($this->student, $this->classroom);
    $second = $service->enroll($this->student, $this->classroom);

    expect($second->getKey())->toBe($first->getKey())
        ->and(ClassroomMember::query()->count())->toBe(1);
});

it('refuses to enroll a non-student account', function (): void {
    $teacher = User::factory()->teacher()->create();

    app(ClassroomService::class)->enroll($teacher, $this->classroom);
})->throws(ValidationException::class);

it('refuses a second active classroom in the same academic year', function (): void {
    $service = app(ClassroomService::class);

    $service->enroll($this->student, $this->classroom);

    $service->enroll($this->student, $this->otherClassroom);
})->throws(ValidationException::class);

it('enforces classroom capacity', function (): void {
    $service = app(ClassroomService::class);

    $service->enroll(User::factory()->student()->create(), $this->classroom);
    $service->enroll(User::factory()->student()->create(), $this->classroom);

    $service->enroll($this->student, $this->classroom);
})->throws(ValidationException::class);

it('transfers a student while keeping the previous placement as history', function (): void {
    $service = app(ClassroomService::class);

    $original = $service->enroll($this->student, $this->classroom, now()->subMonth());

    $newMembership = $service->transfer($this->student, $this->classroom, $this->otherClassroom);

    $original->refresh();

    expect($original->status)->toBe(ClassroomMemberStatus::Transferred)
        ->and($original->left_at)->not->toBeNull()
        ->and($newMembership->classroom_id)->toBe($this->otherClassroom->getKey())
        ->and($newMembership->status)->toBe(ClassroomMemberStatus::Active)
        ->and(ClassroomMember::query()->where('user_id', $this->student->getKey())->count())->toBe(2);
});

it('refuses to transfer a student who is not in the source classroom', function (): void {
    app(ClassroomService::class)->transfer($this->student, $this->classroom, $this->otherClassroom);
})->throws(ValidationException::class);

it('closes a membership without deleting it', function (): void {
    $service = app(ClassroomService::class);
    $membership = $service->enroll($this->student, $this->classroom);

    $service->remove($membership, ClassroomMemberStatus::Dropped);

    expect($membership->fresh()->status)->toBe(ClassroomMemberStatus::Dropped)
        ->and($membership->fresh()->left_at)->not->toBeNull()
        ->and(ClassroomMember::query()->count())->toBe(1);
});

it('exposes the active classroom of the current year', function (): void {
    app(ClassroomService::class)->enroll($this->student, $this->classroom);

    expect($this->student->activeClassroom()?->getKey())->toBe($this->classroom->getKey());
});
