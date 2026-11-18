<?php

use App\Filament\Resources\Assignments\Pages\CreateAssignment;
use App\Filament\Resources\Assignments\Pages\EditAssignment;
use App\Filament\Resources\Assignments\Pages\ListAssignments;
use App\Filament\Resources\Assignments\RelationManagers\SubmissionsRelationManager;
use App\Models\Assignment;
use App\Models\User;
use App\Services\Learning\AssignmentService;
use App\Services\Learning\AssignmentSubmissionService;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 1);
    $this->fixture->publishAll();
    $this->admin = User::factory()->superAdmin()->create();

    $this->assignment = app(AssignmentService::class)->create($this->fixture->course, [
        'title' => 'Tugas HTML',
        'instructions' => 'Buat halaman profil.',
        'due_at' => now()->addDays(3),
    ]);
});

it('renders the assignment resource pages', function (string $path): void {
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with([
    '/admin/assignments',
    '/admin/assignments/create',
]);

it('creates an assignment through the admin page as a draft', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(CreateAssignment::class)
        ->fillForm([
            'course_id' => $this->fixture->course->getKey(),
            'title' => 'Tugas CSS',
            'instructions' => 'Terapkan tema warna.',
            'due_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'max_score' => 100,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $assignment = Assignment::query()->where('title', 'Tugas CSS')->firstOrFail();

    expect($assignment->is_published)->toBeFalse()
        ->and($assignment->course_id)->toBe($this->fixture->course->getKey());
});

it('publishes an assignment from the table action', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(ListAssignments::class)
        ->callTableAction('publish', $this->assignment->getKey())
        ->assertHasNoTableActionErrors();

    expect($this->assignment->fresh()->is_published)->toBeTrue();
});

it('renders the assignment view page with its submissions relation manager', function (): void {
    $this->actingAs($this->admin)
        ->get("/admin/assignments/{$this->assignment->getKey()}")
        ->assertOk();

    Livewire\Livewire::test(SubmissionsRelationManager::class, [
        'ownerRecord' => $this->assignment,
        'pageClass' => EditAssignment::class,
    ])->assertOk();
});

it('grades a submission from the submissions relation manager', function (): void {
    app(AssignmentService::class)->publish($this->assignment);

    $submission = app(AssignmentSubmissionService::class)->submit(
        $this->assignment,
        $this->fixture->students[0],
        ['content' => 'Jawaban siswa.'],
    );

    $this->actingAs($this->admin);

    Livewire\Livewire::test(SubmissionsRelationManager::class, [
        'ownerRecord' => $this->assignment,
        'pageClass' => EditAssignment::class,
    ])
        ->callTableAction('grade', $submission->getKey(), data: [
            'score' => 85,
            'feedback' => 'Bagus.',
        ])
        ->assertHasNoTableActionErrors();

    expect((float) $submission->refresh()->grade->score)->toBe(85.0)
        ->and($submission->status->value)->toBe('graded');
});
