<?php

namespace App\Services\Grading;

use App\Models\Course;
use App\Models\Grade;
use App\Models\QuizAttempt;
use App\Models\SubmissionGrade;
use Illuminate\Support\Facades\DB;

class GradeSyncService
{
    public function __construct(
        private readonly GradeComponentService $components,
        private readonly GradeCalculationService $calculation,
    ) {}

    /**
     * Copy a graded assignment submission into the course gradebook.
     */
    public function syncAssignment(SubmissionGrade $submissionGrade): ?Grade
    {
        $submissionGrade->loadMissing(['submission.assignment', 'submission.student']);
        $submission = $submissionGrade->submission;
        $assignment = $submission?->assignment;
        $student = $submission?->student;

        if ($assignment === null || $student === null) {
            return null;
        }

        $component = $this->components->syncAssignment($assignment);

        if ($component === null) {
            return null;
        }

        return DB::transaction(function () use ($component, $assignment, $submissionGrade, $student): Grade {
            $score = (float) $submissionGrade->score;
            $max = max((float) $component->max_score, 0.01);

            $grade = Grade::query()->updateOrCreate(
                ['grade_component_id' => $component->getKey(), 'user_id' => $student->getKey()],
                [
                    'course_id' => $assignment->course_id,
                    'score' => $score,
                    'max_score' => $max,
                    'percentage' => round($score / $max * 100, 2),
                    'graded_by' => $submissionGrade->graded_by,
                    'is_published' => true,
                ],
            );

            $this->calculation->recalculateForUser($assignment->course, $student);

            return $grade;
        });
    }

    /**
     * Copy a completed quiz attempt into the gradebook.
     */
    public function syncQuizAttempt(QuizAttempt $attempt): ?Grade
    {
        $attempt->loadMissing(['quiz', 'student']);
        $quiz = $attempt->quiz;
        $student = $attempt->student;

        if ($quiz === null || $student === null || $attempt->score === null) {
            return null;
        }

        $component = $this->components->syncQuiz($quiz);

        if ($component === null) {
            return null;
        }

        return DB::transaction(function () use ($component, $attempt, $quiz, $student): Grade {
            $score = min((float) $attempt->score, max((float) $component->max_score, 0.01));
            $max = max((float) $component->max_score, 0.01);

            $grade = Grade::query()->updateOrCreate(
                ['grade_component_id' => $component->getKey(), 'user_id' => $student->getKey()],
                [
                    'course_id' => $quiz->course_id,
                    'score' => $score,
                    'max_score' => $max,
                    'percentage' => round($score / $max * 100, 2),
                    'is_published' => true,
                ],
            );

            $this->calculation->recalculateForUser($quiz->course, $student);

            return $grade;
        });
    }

    /**
     * Backfill every grade source for a course (used by the seeder).
     */
    public function syncCourse(Course $course): void
    {
        $this->components->syncCourse($course);

        $course->assignments()
            ->with(['submissions.grade', 'submissions.student'])
            ->get()
            ->each(function ($assignment): void {
                $assignment->submissions->each(function ($submission): void {
                    if ($submission->grade !== null) {
                        $this->syncAssignment($submission->grade);
                    }
                });
            });

        $course->quizzes()
            ->with(['attempts.student'])
            ->get()
            ->each(function ($quiz): void {
                $quiz->attempts->each(fn (QuizAttempt $attempt) => $this->syncQuizAttempt($attempt));
            });

        $this->calculation->recalculateCourse($course);
    }
}
