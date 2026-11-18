<?php

use App\Models\GradeHistory;
use App\Models\User;
use App\Services\Grading\GradeCalculationService;
use Tests\Support\GradingFixture;

beforeEach(function (): void {
    $this->fixture = GradingFixture::make();
    $this->student = $this->fixture->students[0];
    $this->teacher = $this->fixture->teacher;
    $this->grade = $this->fixture->grade($this->fixture->assignmentComponent, $this->student, 80);
    app(GradeCalculationService::class)->recalculateForUser($this->fixture->course, $this->student);
});

it('renders the teacher gradebook and scheme pages', function (): void {
    $this->actingAs($this->teacher)
        ->get('/guru/grades')
        ->assertOk()
        ->assertSee('Buku Nilai');

    $this->actingAs($this->teacher)
        ->get("/guru/courses/{$this->fixture->course->getKey()}/grades")
        ->assertOk()
        ->assertSee($this->student->name)
        ->assertSee('Tugas Struktur HTML');

    $this->actingAs($this->teacher)
        ->get("/guru/courses/{$this->fixture->course->getKey()}/grading")
        ->assertOk()
        ->assertSee('Bobot kategori');
});

it('updates a grade with an audit reason from the gradebook', function (): void {
    $this->actingAs($this->teacher)
        ->put("/guru/grades/{$this->grade->getKey()}", [
            'score' => 95,
            'reason' => 'Koreksi penilaian',
            'is_published' => true,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect((float) $this->grade->fresh()->score)->toBe(95.0)
        ->and(GradeHistory::query()->where('grade_id', $this->grade->getKey())->count())->toBe(1);
});

it('blocks a teacher from grading another course', function (): void {
    $other = User::factory()->teacher()->create();

    $this->actingAs($other)
        ->put("/guru/grades/{$this->grade->getKey()}", ['score' => 10])
        ->assertForbidden();
});

it('blocks a student from updating grades', function (): void {
    $this->actingAs($this->student)
        ->put("/guru/grades/{$this->grade->getKey()}", ['score' => 100])
        ->assertForbidden();
});

it('shows students only their own published grades', function (): void {
    $this->fixture->grade($this->fixture->quizComponent, $this->student, 40, published: false);

    $this->actingAs($this->student)
        ->get('/student/grades')
        ->assertOk()
        ->assertSee($this->fixture->course->title)
        ->assertSee('32');

    $this->actingAs($this->student)
        ->get("/student/grades/{$this->fixture->course->getKey()}")
        ->assertOk()
        ->assertSee('Tugas Struktur HTML')
        ->assertDontSee('Kuis HTML');
});

it('forbids a student outside the course from viewing its grades', function (): void {
    $outsider = User::factory()->student()->create();

    $this->actingAs($outsider)
        ->get("/student/grades/{$this->fixture->course->getKey()}")
        ->assertForbidden();
});

it('exports the gradebook as csv', function (): void {
    $response = $this->actingAs($this->teacher)
        ->get("/guru/courses/{$this->fixture->course->getKey()}/grades/export")
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    expect($response->streamedContent())
        ->toContain('Nama,Email')
        ->toContain('Nilai Akhir')
        ->toContain($this->student->name);
});
