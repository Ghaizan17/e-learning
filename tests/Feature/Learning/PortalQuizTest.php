<?php

use App\Enums\QuizAttemptStatus;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\Learning\QuizAttemptService;
use Tests\Support\QuizFixture;

beforeEach(function (): void {
    $this->fixture = QuizFixture::make();
    $this->student = $this->fixture->student;
    $this->teacher = $this->fixture->teacher;
});

it('lists published quizzes to enrolled students', function (): void {
    $this->actingAs($this->student)
        ->get('/student/quizzes')
        ->assertOk()
        ->assertSee('Kuis HTML');

    $this->actingAs($this->student)
        ->get("/student/quizzes/{$this->fixture->quiz->getKey()}")
        ->assertOk()
        ->assertSee('Kuis HTML')
        ->assertSee('15');
});

it('starts an attempt from the quiz landing page', function (): void {
    $this->actingAs($this->student)
        ->post("/student/quizzes/{$this->fixture->quiz->getKey()}/start")
        ->assertRedirect();

    $attempt = QuizAttempt::query()->firstOrFail();

    expect($attempt->user_id)->toBe($this->student->getKey())
        ->and($attempt->status)->toBe(QuizAttemptStatus::InProgress);
});

it('renders the attempt page without correctness metadata', function (): void {
    $attempt = $this->fixture->start();

    $response = $this->actingAs($this->student)
        ->get("/student/quiz-attempts/{$attempt->getKey()}")
        ->assertOk()
        ->assertSee('Tag untuk tautan?');

    expect($response->getContent())
        ->not->toContain('is_correct')
        ->not->toContain('score_awarded');
});

it('autosaves an answer through the json endpoint', function (): void {
    $attempt = $this->fixture->start();

    $this->actingAs($this->student)
        ->postJson("/student/quiz-attempts/{$attempt->getKey()}/answers", [
            'question_id' => $this->fixture->choice->getKey(),
            'question_option_id' => $this->fixture->correctOption->getKey(),
        ])
        ->assertOk()
        ->assertJson(['ok' => true]);

    expect($attempt->answers()->count())->toBe(1);
});

it('blocks other students from saving answers to an attempt', function (): void {
    $attempt = $this->fixture->start();
    $other = $this->fixture->learning->students[1];

    $this->actingAs($other)
        ->postJson("/student/quiz-attempts/{$attempt->getKey()}/answers", [
            'question_id' => $this->fixture->choice->getKey(),
            'question_option_id' => $this->fixture->correctOption->getKey(),
        ])
        ->assertForbidden();
});

it('submits answers from the portal and shows the result', function (): void {
    $attempt = $this->fixture->start();

    $this->actingAs($this->student)
        ->post("/student/quiz-attempts/{$attempt->getKey()}/submit", [
            'answers' => [$this->fixture->choice->getKey() => $this->fixture->correctOption->getKey()],
            'answers_text' => [$this->fixture->essay->getKey() => 'Margin di luar.'],
        ])
        ->assertRedirect("/student/quiz-attempts/{$attempt->getKey()}/result");

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Submitted)
        ->and((float) $attempt->fresh()->score)->toBe(40.0);

    $this->actingAs($this->student)
        ->get("/student/quiz-attempts/{$attempt->getKey()}/result")
        ->assertOk()
        ->assertSee('40')
        ->assertSee('<a>');
});

it('hides results until the quiz ends when immediate results are disabled', function (): void {
    $this->fixture->quiz->update([
        'show_result_immediately' => false,
        'ends_at' => now()->addDay(),
    ]);

    $attempt = $this->fixture->start();
    app(QuizAttemptService::class)->submit($attempt);

    $this->actingAs($this->student)
        ->get("/student/quiz-attempts/{$attempt->getKey()}/result")
        ->assertRedirect("/student/quizzes/{$this->fixture->quiz->getKey()}");
});

it('rejects submission after the server deadline', function (): void {
    $attempt = $this->fixture->start();
    $attempt->update(['expires_at' => now()->subMinute()]);

    $this->actingAs($this->student)
        ->post("/student/quiz-attempts/{$attempt->getKey()}/submit", [])
        ->assertRedirect("/student/quiz-attempts/{$attempt->getKey()}/result")
        ->assertSessionHasErrors('quiz');

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Expired);
});

it('lets the teacher review attempts but not other students', function (): void {
    $attempt = $this->fixture->start();

    $this->actingAs($this->teacher)
        ->get("/teacher/quiz-attempts/{$attempt->getKey()}")
        ->assertOk()
        ->assertSee($this->student->name);

    $other = $this->fixture->learning->students[1];

    $this->actingAs($other)
        ->get("/teacher/quiz-attempts/{$attempt->getKey()}")
        ->assertForbidden();
});

it('builds and publishes a quiz from the teacher portal', function (): void {
    $this->actingAs($this->teacher)
        ->post('/teacher/quizzes', [
            'course_id' => $this->fixture->learning->course->getKey(),
            'title' => 'Kuis Baru',
            'type' => 'quiz',
            'duration_minutes' => 10,
            'max_attempts' => 1,
            'passing_score' => 60,
        ])
        ->assertRedirect();

    $quiz = Quiz::query()->where('title', 'Kuis Baru')->firstOrFail();

    $this->actingAs($this->teacher)
        ->post("/teacher/quizzes/{$quiz->getKey()}/questions", [
            'type' => 'multiple_choice',
            'question_text' => 'Ibu kota Indonesia?',
            'score' => 10,
            'options' => [
                ['option_text' => 'Jakarta'],
                ['option_text' => 'Bandung'],
            ],
            'correct' => 0,
        ])
        ->assertSessionHasNoErrors();

    $this->actingAs($this->teacher)
        ->post("/teacher/quizzes/{$quiz->getKey()}/publish")
        ->assertSessionHasNoErrors();

    expect($quiz->fresh()->is_published)->toBeTrue()
        ->and($quiz->questions()->count())->toBe(1);
});

it('grades an essay from the teacher review page', function (): void {
    $attempt = $this->fixture->start();

    app(QuizAttemptService::class)->saveAnswer($attempt, $this->fixture->essay, ['answer_text' => 'Jawaban esai.']);
    app(QuizAttemptService::class)->submit($attempt);

    $answer = $attempt->answers()->where('question_id', $this->fixture->essay->getKey())->firstOrFail();

    $this->actingAs($this->teacher)
        ->post("/teacher/quiz-answers/{$answer->getKey()}/grade", ['score' => 55])
        ->assertSessionHasNoErrors();

    expect((float) $answer->fresh()->score_awarded)->toBe(55.0)
        ->and($attempt->fresh()->status)->toBe(QuizAttemptStatus::Graded)
        ->and($this->student->notifications()->where('data->type', 'quiz_graded')->count())->toBe(1);
});

it('keeps shuffled question order stable for an attempt', function (): void {
    $this->fixture->quiz->update(['shuffle_questions' => true, 'shuffle_options' => true]);

    $attempt = $this->fixture->start();
    $attempts = app(QuizAttemptService::class);

    $first = collect($attempts->orderedQuestions($attempt))->pluck('id')->all();
    $second = collect($attempts->orderedQuestions($attempt->fresh()))->pluck('id')->all();

    expect($second)->toBe($first);
});

it('refuses starting a quiz for a student outside the course', function (): void {
    $outsider = User::factory()->student()->create();

    $this->actingAs($outsider)
        ->post("/student/quizzes/{$this->fixture->quiz->getKey()}/start")
        ->assertForbidden();
});

it('accepts multiple selected options when submitting a multi-select quiz', function (): void {
    $quizzes = app(App\Services\Learning\QuizService::class);
    $questions = app(App\Services\Learning\QuestionService::class);

    $quiz = $quizzes->create($this->fixture->learning->course, [
        'title' => 'Kuis Multi Pilihan',
        'duration_minutes' => 10,
        'max_attempts' => 1,
    ]);

    $question = $questions->create($quiz, [
        'type' => 'multiple_select',
        'question_text' => 'Pilih semua elemen semantik.',
        'score' => 10,
        'options' => [
            ['option_text' => 'header', 'is_correct' => true],
            ['option_text' => 'main', 'is_correct' => true],
            ['option_text' => 'font', 'is_correct' => false],
        ],
    ]);

    $quizzes->publish($quiz);
    $attempt = app(QuizAttemptService::class)->start($quiz, $this->student);
    $correctIds = $question->options->where('is_correct', true)->pluck('id')->all();

    $this->actingAs($this->student)
        ->post("/student/quiz-attempts/{$attempt->getKey()}/submit", [
            'answers' => [$question->getKey() => $correctIds],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect("/student/quiz-attempts/{$attempt->getKey()}/result");

    expect($attempt->fresh()->status)->toBe(QuizAttemptStatus::Graded)
        ->and((float) $attempt->fresh()->score)->toBe(10.0);
});

it('rejects submitting an unknown option id', function (): void {
    $attempt = $this->fixture->start();

    $this->actingAs($this->student)
        ->post("/student/quiz-attempts/{$attempt->getKey()}/submit", [
            'answers' => [$this->fixture->choice->getKey() => 999999],
        ])
        ->assertSessionHasErrors('answers.'.$this->fixture->choice->getKey());
});
