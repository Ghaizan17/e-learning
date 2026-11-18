<?php

namespace App\Services\Grading;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Grade;
use App\Models\User;

class GradeCalculationService
{
    public function __construct(private readonly GradingSchemeService $scheme) {}

    /**
     * Recalculate a student's weighted final score from published grades.
     */
    public function recalculateEnrollment(Enrollment $enrollment): Enrollment
    {
        $course = $enrollment->course;

        if ($course === null) {
            return $enrollment;
        }

        $grades = Grade::query()
            ->where('course_id', $course->getKey())
            ->where('user_id', $enrollment->user_id)
            ->where('is_published', true)
            ->with('component')
            ->get()
            ->groupBy(fn (Grade $grade): int => (int) $grade->component?->grading_category_id);

        $weighted = 0.0;
        $hasGrades = false;

        foreach ($this->scheme->categories($course) as $category) {
            $categoryGrades = $grades->get($category->getKey());

            if ($categoryGrades === null || $categoryGrades->isEmpty()) {
                continue;
            }

            $hasGrades = true;
            $average = (float) $categoryGrades->avg(fn (Grade $grade): float => (float) $grade->percentage);
            $weighted += $average * ((float) $category->weight / 100);
        }

        $finalScore = $hasGrades ? round($weighted, 2) : null;

        $enrollment->update([
            'final_score' => $finalScore,
            'letter_grade' => $finalScore === null ? null : $this->letterGrade($finalScore),
        ]);

        return $enrollment->refresh();
    }

    public function recalculateCourse(Course $course): int
    {
        $count = 0;

        $course->enrollments()->chunkById(100, function ($enrollments) use (&$count): void {
            foreach ($enrollments as $enrollment) {
                $this->recalculateEnrollment($enrollment);
                $count++;
            }
        });

        return $count;
    }

    public function recalculateForUser(Course $course, User $student): ?Enrollment
    {
        $enrollment = $course->enrollments()->where('user_id', $student->getKey())->first();

        return $enrollment === null ? null : $this->recalculateEnrollment($enrollment);
    }

    public function letterGrade(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 80 => 'B',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'E',
        };
    }

    /**
     * Student-facing breakdown of published grades grouped by category.
     *
     * @return array{final_score: ?float, letter_grade: ?string, categories: array<int, array<string, mixed>>}
     */
    public function breakdown(Course $course, User $student): array
    {
        $grades = Grade::query()
            ->where('course_id', $course->getKey())
            ->where('user_id', $student->getKey())
            ->where('is_published', true)
            ->with('component')
            ->get()
            ->groupBy(fn (Grade $grade): int => (int) $grade->component?->grading_category_id);

        $enrollment = $course->enrollments()->where('user_id', $student->getKey())->first();

        $categories = $this->scheme->categories($course)->map(function ($category) use ($grades): array {
            $categoryGrades = $grades->get($category->getKey()) ?? collect();

            return [
                'category' => $category,
                'average' => $categoryGrades->isEmpty()
                    ? null
                    : round((float) $categoryGrades->avg(fn (Grade $grade): float => (float) $grade->percentage), 2),
                'grades' => $categoryGrades->values(),
            ];
        })->all();

        return [
            'final_score' => $enrollment?->final_score === null ? null : (float) $enrollment->final_score,
            'letter_grade' => $enrollment?->letter_grade,
            'categories' => $categories,
        ];
    }
}
