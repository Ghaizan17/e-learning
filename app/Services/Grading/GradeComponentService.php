<?php

namespace App\Services\Grading;

use App\Enums\GradingCategoryName;
use App\Enums\QuizType;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\GradeComponent;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Model;

class GradeComponentService
{
    public function __construct(private readonly GradingSchemeService $scheme) {}

    public function syncAssignment(Assignment $assignment): ?GradeComponent
    {
        $assignment->loadMissing('course');
        $course = $assignment->course;

        if ($course === null) {
            return null;
        }

        return $this->upsert(
            $assignment,
            $course,
            GradingCategoryName::Assignment,
            $assignment->title,
            (float) $assignment->max_score,
        );
    }

    public function syncQuiz(Quiz $quiz): ?GradeComponent
    {
        $quiz->loadMissing('course');
        $course = $quiz->course;

        if ($course === null) {
            return null;
        }

        $category = $quiz->type === QuizType::Exam
            ? GradingCategoryName::Exam
            : GradingCategoryName::Quiz;

        return $this->upsert($quiz, $course, $category, $quiz->title, (float) $quiz->totalPoints());
    }

    public function syncCourse(Course $course): void
    {
        $course->assignments()->get()->each(fn (Assignment $assignment) => $this->syncAssignment($assignment));
        $course->quizzes()->get()->each(fn (Quiz $quiz) => $this->syncQuiz($quiz));
    }

    private function upsert(
        Model $gradeable,
        Course $course,
        GradingCategoryName $categoryName,
        string $title,
        float $maxScore,
    ): GradeComponent {
        $this->scheme->ensureDefaultScheme($course);

        $category = $course->gradingCategories()->where('name', $categoryName->value)->firstOrFail();

        return GradeComponent::query()->updateOrCreate(
            [
                'gradeable_type' => $gradeable->getMorphClass(),
                'gradeable_id' => $gradeable->getKey(),
            ],
            [
                'course_id' => $course->getKey(),
                'grading_category_id' => $category->getKey(),
                'title' => $title,
                'max_score' => max($maxScore, 0.01),
            ],
        );
    }
}
