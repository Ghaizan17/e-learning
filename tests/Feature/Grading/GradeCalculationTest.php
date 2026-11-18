<?php

use App\Models\Grade;
use App\Models\GradeHistory;
use App\Models\Submission;
use App\Services\Grading\GradeAuditService;
use App\Services\Grading\GradeCalculationService;
use App\Services\Learning\AssignmentSubmissionService;
use App\Services\Learning\QuizAttemptService;
use Illuminate\Validation\ValidationException;
use Tests\Support\GradingFixture;

beforeEach(function (): void {
    $this->fixture = GradingFixture::make();
    $this->student = $this->fixture->students[0];
    $this->calculation = app(GradeCalculationService::class);
});

it('computes a weighted final score and letter grade', function (): void {
    $this->fixture->grade($this->fixture->assignmentComponent, $this->student, 80);
    $this->fixture->grade($this->fixture->quizComponent, $this->student, 90);
    $this->fixture->grade($this->fixture->examComponent, $this->student, 100);

    $enrollment = $this->calculation->recalculateForUser($this->fixture->course, $this->student);

    expect((float) $enrollment->final_score)->toBe(89.0)
        ->and($enrollment->letter_grade)->toBe('B');
});

it('excludes unpublished grades from the final score and student breakdown', function (): void {
    $this->fixture->grade($this->fixture->assignmentComponent, $this->student, 80);
    $this->fixture->grade($this->fixture->quizComponent, $this->student, 50, published: false);
    $this->fixture->grade($this->fixture->examComponent, $this->student, 100);

    $enrollment = $this->calculation->recalculateForUser($this->fixture->course, $this->student);
    $breakdown = $this->calculation->breakdown($this->fixture->course, $this->student);

    expect((float) $enrollment->final_score)->toBe(62.0)
        ->and(collect($breakdown['categories'])->flatMap(fn ($entry) => $entry['grades'])->count())->toBe(2);
});

it('records an audit history row and recalculates after a manual edit', function (): void {
    $grade = $this->fixture->grade($this->fixture->assignmentComponent, $this->student, 70);

    app(GradeAuditService::class)->updateGrade($grade, $this->fixture->teacher, [
        'score' => 90,
        'reason' => 'Revisi jawaban',
        'is_published' => true,
    ]);

    $history = GradeHistory::query()->firstOrFail();

    expect((float) $grade->fresh()->score)->toBe(90.0)
        ->and((float) $history->old_score)->toBe(70.0)
        ->and((float) $history->new_score)->toBe(90.0)
        ->and($history->reason)->toBe('Revisi jawaban')
        ->and((float) $this->fixture->course->enrollments()->where('user_id', $this->student->getKey())->value('final_score'))->toBe(36.0);
});

it('rejects a manual score above the component maximum', function (): void {
    $grade = $this->fixture->grade($this->fixture->assignmentComponent, $this->student, 70);

    app(GradeAuditService::class)->updateGrade($grade, $this->fixture->teacher, ['score' => 150]);
})->throws(ValidationException::class);

it('syncs a manually graded assignment into the gradebook', function (): void {
    $submission = Submission::factory()->for($this->fixture->assignment)->create([
        'user_id' => $this->student->getKey(),
        'is_latest' => true,
    ]);

    app(AssignmentSubmissionService::class)->grade($submission, $this->fixture->teacher, 85, 'Bagus');

    $grade = Grade::query()
        ->where('user_id', $this->student->getKey())
        ->where('grade_component_id', $this->fixture->assignmentComponent->getKey())
        ->firstOrFail();

    expect((float) $grade->score)->toBe(85.0)
        ->and((float) $grade->percentage)->toBe(85.0)
        ->and($grade->is_published)->toBeTrue();
});

it('syncs a graded quiz attempt into the gradebook', function (): void {
    $attempt = app(QuizAttemptService::class)->start($this->fixture->quiz, $this->student);
    $option = $this->fixture->quiz->questions()->firstOrFail()->options()->where('is_correct', true)->firstOrFail();

    app(QuizAttemptService::class)->saveAnswer($attempt, $attempt->quiz->questions()->first(), [
        'question_option_id' => $option->getKey(),
    ]);
    app(QuizAttemptService::class)->submit($attempt);

    $grade = Grade::query()
        ->where('user_id', $this->student->getKey())
        ->where('grade_component_id', $this->fixture->quizComponent->getKey())
        ->firstOrFail();

    expect((float) $grade->score)->toBe(100.0);
});

it('maps letter grades at the documented thresholds', function (): void {
    expect($this->calculation->letterGrade(90))->toBe('A')
        ->and($this->calculation->letterGrade(89.99))->toBe('B')
        ->and($this->calculation->letterGrade(70))->toBe('C')
        ->and($this->calculation->letterGrade(60))->toBe('D')
        ->and($this->calculation->letterGrade(59.99))->toBe('E');
});
