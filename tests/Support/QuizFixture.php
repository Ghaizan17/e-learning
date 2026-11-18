<?php

namespace Tests\Support;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\Learning\QuestionService;
use App\Services\Learning\QuizAttemptService;
use App\Services\Learning\QuizService;

/**
 * Builds a published course + quiz with one auto-graded and one essay question.
 */
class QuizFixture
{
    public LearningFixture $learning;

    public Quiz $quiz;

    public Question $choice;

    public Question $essay;

    public QuestionOption $correctOption;

    public User $student;

    public User $teacher;

    private function __construct() {}

    public static function make(): self
    {
        $fixture = new self;
        $fixture->learning = LearningFixture::make(studentCount: 2);
        $fixture->learning->publishAll();
        $fixture->student = $fixture->learning->students[0];
        $fixture->teacher = $fixture->learning->teacher;

        $quizzes = app(QuizService::class);
        $questions = app(QuestionService::class);

        $fixture->quiz = $quizzes->create($fixture->learning->course, [
            'title' => 'Kuis HTML',
            'duration_minutes' => 30,
            'max_attempts' => 2,
            'passing_score' => 70,
        ]);

        $fixture->choice = $questions->create($fixture->quiz, [
            'type' => 'multiple_choice',
            'question_text' => 'Tag untuk tautan?',
            'score' => 40,
            'options' => [
                ['option_text' => '<a>', 'is_correct' => true],
                ['option_text' => '<p>', 'is_correct' => false],
            ],
        ]);

        $fixture->correctOption = $fixture->choice->options->firstWhere('is_correct', true);

        $fixture->essay = $questions->create($fixture->quiz, [
            'type' => 'essay',
            'question_text' => 'Jelaskan margin dan padding.',
            'score' => 60,
        ]);

        $quizzes->publish($fixture->quiz);

        return $fixture;
    }

    public function start(): QuizAttempt
    {
        return app(QuizAttemptService::class)->start($this->quiz, $this->student);
    }
}
