<?php

use App\Enums\QuizAttemptStatus;
use App\Jobs\ExpireQuizAttempts;
use App\Models\QuestionOption;
use App\Services\Learning\QuestionService;
use App\Services\Learning\QuizAttemptService;
use App\Services\Learning\QuizGradingService;
use App\Services\Learning\QuizService;
use Illuminate\Validation\ValidationException;
use Tests\Support\QuizFixture;

beforeEach(function (): void {
    $this->fixture = QuizFixture::make();
    $this->service = app(QuizAttemptService::class);
});

it('starts an attempt with a deadline capped by the quiz end', function (): void {
    $this->fixture->quiz->update(['ends_at' => now()->addMinutes(10)]);

    $attempt = $this->service->start($this->fixture->quiz->fresh(), $this->fixture->student);

    expect($attempt->status)->toBe(QuizAttemptStatus::InProgress)
        ->and($attempt->attempt_no)->toBe(1)
        ->and($attempt->expires_at->lessThanOrEqualTo($this->fixture->quiz->fresh()->ends_at))->toBeTrue();
});

it('refuses to start before the quiz opens and after it closes', function (): void {
    $this->fixture->quiz->update(['starts_at' => now()->addHour()]);
    $this->service->start($this->fixture->quiz->fresh(), $this->fixture->student);
})->throws(ValidationException::class);

it('refuses to start after the quiz end', function (): void {
    $this->fixture->quiz->update(['starts_at' => now()->subHour(), 'ends_at' => now()->subMinute()]);
    $this->service->start($this->fixture->quiz->fresh(), $this->fixture->student);
})->throws(ValidationException::class);

it('resumes the active attempt instead of creating a new one', function (): void {
    $first = $this->fixture->start();
    $second = $this->fixture->start();

    expect($second->getKey())->toBe($first->getKey())
        ->and($this->fixture->quiz->attempts()->count())->toBe(1);
});

it('enforces the attempt limit', function (): void {
    $this->fixture->quiz->update(['max_attempts' => 1]);

    $attempt = $this->fixture->start();
    $this->service->saveAnswer($attempt, $this->fixture->choice, ['question_option_id' => $this->fixture->correctOption->getKey()]);
    $this->service->submit($attempt);

    $this->service->start($this->fixture->quiz, $this->fixture->student);
})->throws(ValidationException::class);

it('rejects answers and submissions after the server deadline and marks the attempt expired', function (): void {
    $attempt = $this->fixture->start();
    $attempt->update(['expires_at' => now()->subMinute()]);

    try {
        $this->service->submit($attempt);
    } catch (ValidationException $exception) {
        expect($exception->errors()['quiz'][0])->toContain('Waktu');
    }

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Expired);
});

it('rejects a duplicate submission', function (): void {
    $attempt = $this->fixture->start();
    $this->service->submit($attempt);
    $this->service->submit($attempt->fresh());
})->throws(ValidationException::class);

it('auto-grades objective answers and keeps essays pending', function (): void {
    $attempt = $this->fixture->start();

    $this->service->saveAnswer($attempt, $this->fixture->choice, [
        'question_option_id' => $this->fixture->correctOption->getKey(),
    ]);
    $this->service->saveAnswer($attempt, $this->fixture->essay, [
        'answer_text' => 'Margin di luar, padding di dalam.',
    ]);

    $submitted = $this->service->submit($attempt);

    expect((float) $submitted->score)->toBe(40.0)
        ->and($submitted->status)->toBe(QuizAttemptStatus::Submitted)
        ->and($submitted->answers()->whereNotNull('score_awarded')->count())->toBe(1);
});

it('grades multiple select answers as all-or-nothing', function (): void {
    $quiz = app(QuizService::class)->create($this->fixture->learning->course, [
        'title' => 'Kuis Multi',
    ]);

    $question = app(QuestionService::class)->create($quiz, [
        'type' => 'multiple_select',
        'question_text' => 'Pilih semua elemen semantik.',
        'score' => 10,
        'options' => [
            ['option_text' => 'header', 'is_correct' => true],
            ['option_text' => 'main', 'is_correct' => true],
            ['option_text' => 'font', 'is_correct' => false],
        ],
    ]);

    app(QuizService::class)->publish($quiz);

    $attempt = $this->service->start($quiz, $this->fixture->student);
    $correctIds = $question->options->where('is_correct', true)->pluck('id')->all();

    $this->service->saveAnswer($attempt, $question, ['option_ids' => $correctIds]);
    $graded = $this->service->submit($attempt);

    expect((float) $graded->score)->toBe(10.0)
        ->and($graded->status)->toBe(QuizAttemptStatus::Graded);
});

it('grades short answers case-insensitively', function (): void {
    $quiz = app(QuizService::class)->create($this->fixture->learning->course, [
        'title' => 'Kuis Singkat',
    ]);

    $question = app(QuestionService::class)->create($quiz, [
        'type' => 'short_answer',
        'question_text' => 'Ibu kota Indonesia?',
        'score' => 10,
        'options' => [
            ['option_text' => 'Jakarta', 'is_correct' => true],
            ['option_text' => 'Bandung', 'is_correct' => false],
        ],
    ]);

    app(QuizService::class)->publish($quiz);

    $attempt = $this->service->start($quiz, $this->fixture->student);
    $this->service->saveAnswer($attempt, $question, ['answer_text' => '  jakarta ']);
    $graded = $this->service->submit($attempt);

    expect((float) $graded->score)->toBe(10.0)
        ->and($graded->status)->toBe(QuizAttemptStatus::Graded);
});

it('recomputes the attempt when the essay is graded manually', function (): void {
    $attempt = $this->fixture->start();
    $this->service->saveAnswer($attempt, $this->fixture->choice, [
        'question_option_id' => $this->fixture->correctOption->getKey(),
    ]);
    $this->service->saveAnswer($attempt, $this->fixture->essay, ['answer_text' => 'Jawaban esai.']);
    $submitted = $this->service->submit($attempt);

    $essayAnswer = $submitted->answers()->where('question_id', $this->fixture->essay->getKey())->firstOrFail();

    app(QuizGradingService::class)->gradeAnswer($essayAnswer, $this->fixture->teacher, 50);

    expect((float) $submitted->fresh()->score)->toBe(90.0)
        ->and($submitted->fresh()->status)->toBe(QuizAttemptStatus::Graded)
        ->and($this->fixture->student->notifications()->where('data->type', 'quiz_graded')->count())->toBe(1);
});

it('rejects essay scores above the question maximum', function (): void {
    $attempt = $this->fixture->start();
    $this->service->saveAnswer($attempt, $this->fixture->essay, ['answer_text' => 'Jawaban.']);
    $submitted = $this->service->submit($attempt);
    $essayAnswer = $submitted->answers()->where('question_id', $this->fixture->essay->getKey())->firstOrFail();

    app(QuizGradingService::class)->gradeAnswer($essayAnswer, $this->fixture->teacher, 100);
})->throws(ValidationException::class);

it('expires overdue attempts through the scheduled job', function (): void {
    $attempt = $this->fixture->start();
    $attempt->update(['expires_at' => now()->subMinutes(2)]);

    (new ExpireQuizAttempts)->handle($this->service);

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Expired);
});

it('caches the quiz used by the fixture', function (): void {
    expect($this->fixture->quiz->is_published)->toBeTrue()
        ->and($this->fixture->quiz->questions()->count())->toBe(2)
        ->and($this->fixture->quiz->totalPoints())->toEqual(100.0);
});

it('does not expose correctness flags through the answer payload', function (): void {
    $attempt = $this->fixture->start();

    $this->service->saveAnswer($attempt, $this->fixture->choice, [
        'question_option_id' => $this->fixture->correctOption->getKey(),
    ]);

    $answer = $attempt->answers()->firstOrFail();

    expect($answer->is_correct)->toBeNull()
        ->and($answer->score_awarded)->toBeNull();
});

it('rejects options that do not belong to the question', function (): void {
    $attempt = $this->fixture->start();
    $foreign = QuestionOption::factory()->create();

    $this->service->saveAnswer($attempt, $this->fixture->choice, [
        'question_option_id' => $foreign->getKey(),
    ]);
})->throws(ValidationException::class);
