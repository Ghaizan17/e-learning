<?php

use App\Enums\GradeLevel;
use App\Filament\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Filament\Resources\Classrooms\Pages\CreateClassroom;
use App\Filament\Resources\Classrooms\Pages\EditClassroom;
use App\Filament\Resources\Classrooms\RelationManagers\MembersRelationManager;
use App\Filament\Resources\TeacherAssignments\Pages\CreateTeacherAssignment;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use App\Services\Academic\TeacherAssignmentService;

beforeEach(function (): void {
    $this->admin = User::factory()->schoolAdmin()->create();
    $this->year = AcademicYear::factory()->active()->create(['name' => '2026/2027']);
    $this->semester = Semester::factory()->forYear($this->year)->odd()->active()->create();
    $this->classroom = Classroom::factory()->forYear($this->year)->create(['name' => 'XI PPLG A']);
    $this->subject = Subject::factory()->create(['name' => 'Pemrograman Web']);
    $this->teacher = User::factory()->teacher()->create(['name' => 'Bu Rina']);
    $this->student = User::factory()->student()->create(['name' => 'Siswa Uji']);

    app(ClassroomService::class)->enroll($this->student, $this->classroom);
    app(TeacherAssignmentService::class)
        ->assign($this->teacher, $this->classroom, $this->subject, $this->year, $this->semester);
});

it('renders every academic resource list', function (string $path): void {
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with([
    '/admin/academic-years',
    '/admin/semesters',
    '/admin/departments',
    '/admin/subjects',
    '/admin/classrooms',
    '/admin/teacher-assignments',
]);

it('renders the academic year form', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/academic-years/create')
        ->assertOk()
        ->assertSee('Nama tahun ajaran')
        ->assertSee('Tahun ajaran aktif');
});

it('shows the active student count on the classroom view page', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/classrooms/'.$this->classroom->getKey())
        ->assertOk()
        ->assertSee('XI PPLG A')
        ->assertSee('1 / '.$this->classroom->capacity);
});

it('renders the teacher assignment form with a teacher and subject', function (): void {
    $this->actingAs($this->admin)
        ->get('/admin/teacher-assignments/create')
        ->assertOk()
        ->assertSee('Penugasan mengajar')
        ->assertSee('Mata pelajaran');
});

it('activates a year from the table action', function (): void {
    $other = AcademicYear::factory()->create(['name' => '2027/2028']);

    $this->actingAs($this->admin);

    Livewire\Livewire::test(
        ListAcademicYears::class,
    )
        ->callTableAction('activate', $other)
        ->assertHasNoTableActionErrors();

    expect($other->fresh()->is_active)->toBeTrue()
        ->and($this->year->fresh()->is_active)->toBeFalse();
});

it('enrolls a student through the classroom members relation manager', function (): void {
    $newStudent = User::factory()->student()->create(['name' => 'Siswa Baru']);

    $this->actingAs($this->admin);

    Livewire\Livewire::test(
        MembersRelationManager::class,
        ['ownerRecord' => $this->classroom, 'pageClass' => EditClassroom::class],
    )
        ->callTableAction('create', data: [
            'user_id' => $newStudent->getKey(),
            'joined_at' => now()->toDateString(),
        ])
        ->assertHasNoTableActionErrors();

    expect($this->classroom->activeMembers()->where('user_id', $newStudent->getKey())->exists())->toBeTrue();
});

it('blocks duplicate teacher assignments from the create page', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(
        CreateTeacherAssignment::class,
    )
        ->fillForm([
            'academic_year_id' => $this->year->getKey(),
            'semester_id' => $this->semester->getKey(),
            'teacher_id' => $this->teacher->getKey(),
            'subject_id' => $this->subject->getKey(),
            'classroom_id' => $this->classroom->getKey(),
        ])
        ->call('create')
        ->assertHasFormErrors(['teacher_id']);

    expect(TeacherAssignment::query()->count())->toBe(1);
});

it('creates a classroom from the admin form', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(
        CreateClassroom::class,
    )
        ->fillForm([
            'academic_year_id' => $this->year->getKey(),
            'name' => 'XII PPLG B',
            'grade_level' => GradeLevel::Twelve->value,
            'capacity' => 30,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Classroom::query()->where('name', 'XII PPLG B')->exists())->toBeTrue();
});

it('duplicates the active academic year from the list action', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(
        ListAcademicYears::class,
    )
        ->callAction('rollover', data: [
            'name' => '2027/2028',
            'start_date' => '2027-07-01',
            'end_date' => '2028-06-30',
        ])
        ->assertHasNoActionErrors();

    $copied = AcademicYear::query()->where('name', '2027/2028')->first();

    expect($copied)->not->toBeNull()
        ->and($copied->classrooms()->count())->toBe(1)
        ->and($copied->classrooms()->first()->members()->count())->toBe(0);
});
