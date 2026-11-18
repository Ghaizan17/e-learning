<?php

use App\Filament\Resources\Quizzes\Pages\CreateQuiz;
use App\Filament\Resources\Quizzes\Pages\EditQuiz;
use App\Filament\Resources\Quizzes\Pages\ListQuizzes;
use App\Filament\Resources\Quizzes\RelationManagers\QuestionsRelationManager;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Learning\QuestionService;
use App\Services\Learning\QuizService;
use Tests\Support\QuizFixture;

beforeEach(function (): void {
    $this->fixture = QuizFixture::make();
    $this->admin = User::factory()->superAdmin()->create();
});

it('renders the quiz resource pages', function (string $path): void {
    $this->actingAs($this->admin)->get($path)->assertOk();
})->with([
    '/admin/quizzes',
    '/admin/quizzes/create',
]);

it('creates a draft quiz through the admin page', function (): void {
    $this->actingAs($this->admin);

    Livewire\Livewire::test(CreateQuiz::class)
        ->fillForm([
            'course_id' => $this->fixture->learning->course->getKey(),
            'title' => 'Ujian Akhir',
            'type' => 'exam',
            'duration_minutes' => 90,
            'max_attempts' => 1,
            'passing_score' => 75,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $quiz = Quiz::query()->where('title', 'Ujian Akhir')->firstOrFail();

    expect($quiz->is_published)->toBeFalse()
        ->and($quiz->duration_minutes)->toBe(90);
});

it('adds a question with options through the relation manager', function (): void {
    $this->actingAs($this->admin);

    $quiz = app(QuizService::class)->create($this->fixture->learning->course, [
        'title' => 'Kuis Admin',
    ]);

    Livewire\Livewire::test(QuestionsRelationManager::class, [
        'ownerRecord' => $quiz,
        'pageClass' => EditQuiz::class,
    ])
        ->callTableAction('create', data: [
            'type' => 'multiple_choice',
            'question_text' => 'Ibu kota Indonesia?',
            'score' => 10,
            'options' => [
                ['option_text' => 'Jakarta', 'is_correct' => true],
                ['option_text' => 'Bandung', 'is_correct' => false],
            ],
        ])
        ->assertHasNoTableActionErrors();

    expect($quiz->questions()->count())->toBe(1)
        ->and($quiz->questions()->first()->position)->toBe(1);
});

it('publishes a quiz from the table action', function (): void {
    $quiz = app(QuizService::class)->create($this->fixture->learning->course, [
        'title' => 'Kuis Terbit',
    ]);

    $question = app(QuestionService::class)->create($quiz, [
        'type' => 'true_false',
        'question_text' => 'HTML adalah bahasa markup.',
        'score' => 10,
        'options' => [
            ['option_text' => 'Benar', 'is_correct' => true],
            ['option_text' => 'Salah', 'is_correct' => false],
        ],
    ]);

    $this->actingAs($this->admin);

    Livewire\Livewire::test(ListQuizzes::class)
        ->callTableAction('publish', $quiz->getKey())
        ->assertHasNoTableActionErrors();

    expect($quiz->fresh()->is_published)->toBeTrue();
});

it('renders the quiz view page with its questions relation manager', function (): void {
    $this->actingAs($this->admin)
        ->get("/admin/quizzes/{$this->fixture->quiz->getKey()}")
        ->assertOk();

    Livewire\Livewire::test(QuestionsRelationManager::class, [
        'ownerRecord' => $this->fixture->quiz,
        'pageClass' => EditQuiz::class,
    ])->assertOk();
});
