<?php

namespace Tests\Support;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeComponent;
use App\Models\Quiz;
use App\Models\User;
use App\Services\Grading\GradeComponentService;
use App\Services\Learning\AssignmentService;
use App\Services\Learning\QuestionService;
use App\Services\Learning\QuizService;

/**
 * Course with published assignment, quiz and exam components plus enrolled students.
 */
class GradingFixture
{
    public LearningFixture $learning;

    public Course $course;

    public User $teacher;

    /** @var array<int, User> */
    public array $students;

    public Assignment $assignment;

    public Quiz $quiz;

    public Quiz $exam;

    public GradeComponent $assignmentComponent;

    public GradeComponent $quizComponent;

    public GradeComponent $examComponent;

    private function __construct() {}

    public static function make(): self
    {
        $fixture = new self;
        $fixture->learning = LearningFixture::make(studentCount: 2);
        $fixture->learning->publishAll();
        $fixture->course = $fixture->learning->course;
        $fixture->teacher = $fixture->learning->teacher;
        $fixture->students = $fixture->learning->students;

        $assignments = app(AssignmentService::class);
        $quizzes = app(QuizService::class);
        $questions = app(QuestionService::class);

        $fixture->assignment = $assignments->create($fixture->course, [
            'title' => 'Tugas Struktur HTML',
            'instructions' => 'Buat halaman profil.',
            'due_at' => now()->addDays(3)->toDateTimeString(),
            'max_score' => 100,
        ]);
        $assignments->publish($fixture->assignment);

        $fixture->quiz = $quizzes->create($fixture->course, ['title' => 'Kuis HTML']);
        $questions->create($fixture->quiz, [
            'type' => 'multiple_choice',
            'question_text' => 'Tag tautan?',
            'score' => 100,
            'options' => [
                ['option_text' => '<a>', 'is_correct' => true],
                ['option_text' => '<p>', 'is_correct' => false],
            ],
        ]);
        $quizzes->publish($fixture->quiz);

        $fixture->exam = $quizzes->create($fixture->course, [
            'title' => 'Ujian Akhir',
            'type' => 'exam',
        ]);
        $questions->create($fixture->exam, [
            'type' => 'true_false',
            'question_text' => 'HTML adalah bahasa markup.',
            'score' => 100,
            'options' => [
                ['option_text' => 'Benar', 'is_correct' => true],
                ['option_text' => 'Salah', 'is_correct' => false],
            ],
        ]);
        $quizzes->publish($fixture->exam);

        app(GradeComponentService::class)->syncCourse($fixture->course);

        $fixture->assignmentComponent = GradeComponent::query()
            ->where('gradeable_type', $fixture->assignment->getMorphClass())
            ->where('gradeable_id', $fixture->assignment->getKey())
            ->firstOrFail();

        $fixture->quizComponent = GradeComponent::query()
            ->where('gradeable_type', $fixture->quiz->getMorphClass())
            ->where('gradeable_id', $fixture->quiz->getKey())
            ->firstOrFail();

        $fixture->examComponent = GradeComponent::query()
            ->where('gradeable_type', $fixture->exam->getMorphClass())
            ->where('gradeable_id', $fixture->exam->getKey())
            ->firstOrFail();

        return $fixture;
    }

    public function grade(GradeComponent $component, User $student, float $score, bool $published = true): Grade
    {
        return Grade::query()->create([
            'course_id' => $this->course->getKey(),
            'user_id' => $student->getKey(),
            'grade_component_id' => $component->getKey(),
            'score' => $score,
            'max_score' => $component->max_score,
            'percentage' => round($score / (float) $component->max_score * 100, 2),
            'graded_by' => $this->teacher->getKey(),
            'is_published' => $published,
        ]);
    }
}
