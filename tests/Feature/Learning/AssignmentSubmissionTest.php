<?php

use App\Enums\SubmissionStatus;
use App\Jobs\SendAssignmentDeadlineReminders;
use App\Models\Submission;
use App\Models\User;
use App\Services\Learning\AssignmentService;
use App\Services\Learning\AssignmentSubmissionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Support\LearningFixture;

beforeEach(function (): void {
    $this->fixture = LearningFixture::make(studentCount: 2);
    $this->fixture->publishAll();
    $this->student = $this->fixture->students[0];
    $this->service = app(AssignmentSubmissionService::class);

    $this->assignment = app(AssignmentService::class)->create($this->fixture->course, [
        'title' => 'Tugas HTML',
        'instructions' => 'Buat halaman profil.',
        'due_at' => now()->addDays(3),
        'max_score' => 100,
        'allow_resubmission' => false,
    ]);
    app(AssignmentService::class)->publish($this->assignment);
});

it('stores a submission with private files before the deadline', function (): void {
    Storage::fake('local');

    $submission = $this->service->submit($this->assignment, $this->student, [
        'content' => 'Jawaban saya.',
    ], [UploadedFile::fake()->create('tugas.pdf', 120, 'application/pdf')]);

    expect($submission->status)->toBe(SubmissionStatus::Submitted)
        ->and($submission->attempt_no)->toBe(1)
        ->and($submission->is_latest)->toBeTrue()
        ->and($submission->submitted_at)->not->toBeNull()
        ->and($submission->files)->toHaveCount(1);

    Storage::disk('local')->assertExists($submission->files->first()->file_path);
});

it('flags a submission after the deadline as late', function (): void {
    $this->assignment->update(['due_at' => now()->subHour()]);

    $submission = $this->service->submit($this->assignment, $this->student, ['content' => 'Terlambat.']);

    expect($submission->status)->toBe(SubmissionStatus::Late);
});

it('rejects a second attempt when resubmission is disabled', function (): void {
    $this->service->submit($this->assignment, $this->student, ['content' => 'Pertama.']);

    $this->service->submit($this->assignment, $this->student, ['content' => 'Kedua.']);
})->throws(ValidationException::class);

it('creates a new attempt and closes the previous one when resubmission is allowed', function (): void {
    $this->assignment->update(['allow_resubmission' => true]);

    $first = $this->service->submit($this->assignment, $this->student, ['content' => 'Pertama.']);
    $second = $this->service->submit($this->assignment, $this->student, ['content' => 'Kedua.']);

    expect($second->attempt_no)->toBe(2)
        ->and($second->is_latest)->toBeTrue()
        ->and($first->fresh()->is_latest)->toBeFalse()
        ->and(Submission::query()->where('assignment_id', $this->assignment->getKey())->count())->toBe(2);
});

it('rejects submissions from students outside the course', function (): void {
    $outsider = User::factory()->student()->create();

    $this->service->submit($this->assignment, $outsider, ['content' => 'Coba.']);
})->throws(ValidationException::class);

it('rejects submissions for unpublished assignments', function (): void {
    $this->assignment->update(['is_published' => false]);

    $this->service->submit($this->assignment, $this->student, ['content' => 'Coba.']);
})->throws(ValidationException::class);

it('grades a submission, stores feedback, and notifies the student', function (): void {
    $submission = $this->service->submit($this->assignment, $this->student, ['content' => 'Jawaban.']);

    $grade = $this->service->grade($submission, $this->fixture->teacher, 87.5, 'Kerja bagus.');

    expect((float) $grade->score)->toBe(87.5)
        ->and($submission->fresh()->status)->toBe(SubmissionStatus::Graded)
        ->and($submission->grade->feedback)->toBe('Kerja bagus.')
        ->and($this->student->notifications()->where('data->type', 'assignment_graded')->count())->toBe(1);
});

it('rejects scores above the assignment maximum', function (): void {
    $submission = $this->service->submit($this->assignment, $this->student, ['content' => 'Jawaban.']);

    $this->service->grade($submission, $this->fixture->teacher, 120, null);
})->throws(ValidationException::class);

it('notifies only students who have not submitted when the deadline nears', function (): void {
    $this->assignment->update(['due_at' => now()->addHours(6)]);

    $this->service->submit($this->assignment, $this->student, ['content' => 'Sudah.']);

    (new SendAssignmentDeadlineReminders)->handle();

    expect($this->student->notifications()->where('data->type', 'assignment_deadline_reminder')->count())->toBe(0)
        ->and($this->fixture->students[1]->notifications()->where('data->type', 'assignment_deadline_reminder')->count())->toBe(1);

    (new SendAssignmentDeadlineReminders)->handle();

    expect($this->fixture->students[1]->notifications()->where('data->type', 'assignment_deadline_reminder')->count())->toBe(1);
});
