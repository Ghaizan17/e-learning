<?php

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Services\Learning\AssignmentService;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 2);
    $this->fixture->publishAll();
    $this->teacher = $this->fixture->teacher;
    $this->student = $this->fixture->students[0];

    $this->assignment = app(AssignmentService::class)->create($this->fixture->course, [
        'title' => 'Tugas HTML',
        'instructions' => 'Buat halaman profil.',
        'due_at' => now()->addDays(3),
    ]);
});

it('creates an assignment draft from the teacher portal', function (): void {
    $this->actingAs($this->teacher)
        ->post('/guru/assignments', [
            'course_id' => $this->fixture->course->getKey(),
            'title' => 'Tugas CSS',
            'instructions' => 'Terapkan tema warna.',
            'due_at' => now()->addDays(5)->format('Y-m-d\TH:i'),
            'max_score' => 100,
        ])
        ->assertRedirect();

    $assignment = Assignment::query()->where('title', 'Tugas CSS')->firstOrFail();

    expect($assignment->course_id)->toBe($this->fixture->course->getKey())
        ->and($assignment->is_published)->toBeFalse();
});

it('publishes an assignment and notifies enrolled students', function (): void {
    $this->actingAs($this->teacher)
        ->post("/guru/assignments/{$this->assignment->getKey()}/publish")
        ->assertRedirect();

    expect($this->assignment->fresh()->is_published)->toBeTrue()
        ->and($this->fixture->students[0]->notifications()->count())->toBe(1)
        ->and($this->fixture->students[1]->notifications()->count())->toBe(1);
});

it('lists published assignments to the enrolled student', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $this->actingAs($this->student)
        ->get('/student/assignments')
        ->assertOk()
        ->assertSee('Tugas HTML');

    $this->actingAs($this->student)
        ->get("/student/assignments/{$this->assignment->getKey()}")
        ->assertOk()
        ->assertSee('Buat halaman profil.');
});

it('submits an assignment from the student portal', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $this->actingAs($this->student)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", [
            'content' => 'Ini jawaban saya.',
        ])
        ->assertRedirect("/student/assignments/{$this->assignment->getKey()}");

    $submission = Submission::query()->where('assignment_id', $this->assignment->getKey())->firstOrFail();

    expect($submission->user_id)->toBe($this->student->getKey())
        ->and($submission->status)->toBe(SubmissionStatus::Submitted)
        ->and($submission->content)->toBe('Ini jawaban saya.');
});

it('blocks students from other classrooms from viewing or submitting the assignment', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $outsider = User::factory()->student()->create();

    $this->actingAs($outsider)
        ->get("/student/assignments/{$this->assignment->getKey()}")
        ->assertForbidden();

    $this->actingAs($outsider)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", ['content' => 'Coba.'])
        ->assertForbidden();
});

it('shows submissions and grading results to the teacher and student', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $this->actingAs($this->student)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", ['content' => 'Jawaban siswa.']);

    $submission = Submission::query()->firstOrFail();

    $this->actingAs($this->teacher)
        ->get("/guru/assignments/{$this->assignment->getKey()}")
        ->assertOk()
        ->assertSee('Jawaban siswa.');

    $this->actingAs($this->teacher)
        ->post("/guru/submissions/{$submission->getKey()}/grade", [
            'score' => 90,
            'feedback' => 'Mantap.',
        ])
        ->assertRedirect();

    expect((float) $submission->grade->score)->toBe(90.0);

    $this->actingAs($this->student)
        ->get("/student/assignments/{$this->assignment->getKey()}")
        ->assertOk()
        ->assertSee('Mantap.')
        ->assertSee('90');
});

it('refuses grading by a teacher who does not teach the course', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $this->actingAs($this->student)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", ['content' => 'Jawaban.']);

    $submission = Submission::query()->firstOrFail();
    $otherTeacher = User::factory()->teacher()->create();

    $this->actingAs($otherTeacher)
        ->post("/guru/submissions/{$submission->getKey()}/grade", ['score' => 100])
        ->assertForbidden();

    expect($submission->fresh()->status)->not->toBe(SubmissionStatus::Graded);
});

it('rejects a resubmission from the portal when the teacher disabled it', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $this->actingAs($this->student)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", ['content' => 'Pertama.']);

    $this->actingAs($this->student)
        ->post("/student/assignments/{$this->assignment->getKey()}/submit", ['content' => 'Kedua.'])
        ->assertSessionHasErrors('content');

    expect(Submission::query()->count())->toBe(1);
});

it('marks a notification as read and follows its link', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $notification = $this->student->notifications()->firstOrFail();

    $this->actingAs($this->student)
        ->post("/notifications/{$notification->id}/read")
        ->assertRedirect("/student/assignments/{$this->assignment->getKey()}");

    expect($notification->fresh()->read_at)->not->toBeNull();
});
