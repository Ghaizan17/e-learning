<?php

use App\Enums\SemesterName;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Services\Academic\AcademicRolloverService;
use App\Services\Academic\AcademicYearService;
use Illuminate\Validation\ValidationException;

it('keeps only one active academic year', function (): void {
    $previous = AcademicYear::factory()->active()->create(['name' => '2025/2026']);
    $next = AcademicYear::factory()->create(['name' => '2026/2027']);

    app(AcademicYearService::class)->activate($next);

    expect($next->fresh()->is_active)->toBeTrue()
        ->and($previous->fresh()->is_active)->toBeFalse();
});

it('keeps only one active semester across the school', function (): void {
    $year = AcademicYear::factory()->active()->create();
    $odd = Semester::factory()->forYear($year)->odd()->active()->create();
    $even = Semester::factory()->forYear($year)->even()->create();

    $even->update(['is_active' => true]);

    expect($even->fresh()->is_active)->toBeTrue()
        ->and($odd->fresh()->is_active)->toBeFalse();
});

it('deactivates semesters when the year is deactivated', function (): void {
    $year = AcademicYear::factory()->active()->create();
    $semester = Semester::factory()->forYear($year)->odd()->active()->create();

    app(AcademicYearService::class)->deactivate($year);

    expect($year->fresh()->is_active)->toBeFalse()
        ->and($semester->fresh()->is_active)->toBeFalse();
});

it('rolls a year over into a new year without copying members', function (): void {
    $source = AcademicYear::factory()->create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30']);
    Semester::factory()->forYear($source)->odd()->create(['start_date' => '2026-07-01', 'end_date' => '2026-12-31']);
    Semester::factory()->forYear($source)->even()->create(['start_date' => '2027-01-01', 'end_date' => '2027-06-30']);

    $classroom = Classroom::factory()->forYear($source)->create([
        'name' => 'XI PPLG A',
        'capacity' => 32,
    ]);

    $target = app(AcademicRolloverService::class)->rollover($source, [
        'name' => '2027/2028',
        'start_date' => '2027-07-01',
        'end_date' => '2028-06-30',
    ]);

    expect($target->name)->toBe('2027/2028')
        ->and($target->is_active)->toBeFalse()
        ->and($target->semesters)->toHaveCount(2)
        ->and($target->classrooms)->toHaveCount(1)
        ->and($target->classrooms->first()->name)->toBe('XI PPLG A')
        ->and($target->classrooms->first()->homeroom_teacher_id)->toBeNull()
        ->and($target->classrooms->first()->members)->toHaveCount(0)
        ->and($target->semesters->firstWhere('name', SemesterName::Odd)->start_date->toDateString())->toBe('2027-07-01')
        ->and($classroom->fresh()->academic_year_id)->toBe($source->getKey());
});

it('refuses to roll over into an existing year name', function (): void {
    $source = AcademicYear::factory()->create(['name' => '2026/2027']);
    AcademicYear::factory()->create(['name' => '2027/2028']);

    app(AcademicRolloverService::class)->rollover($source, [
        'name' => '2027/2028',
        'start_date' => '2027-07-01',
        'end_date' => '2028-06-30',
    ]);
})->throws(ValidationException::class);
