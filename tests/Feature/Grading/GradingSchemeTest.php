<?php

use App\Enums\GradingCategoryName;
use App\Models\GradeComponent;
use App\Models\Quiz;
use App\Services\Grading\GradingSchemeService;
use App\Services\Learning\CourseService;
use Illuminate\Validation\ValidationException;
use Tests\Support\GradingFixture;
use Tests\Support\LearningFixture;

it('creates a default weighting scheme when a course is created', function (): void {
    $fixture = LearningFixture::make();

    $categories = $fixture->course->gradingCategories()->get();

    expect($categories)->toHaveCount(4)
        ->and((float) $categories->sum('weight'))->toBe(100.0)
        ->and($categories->pluck('name')->all())->toContain(
            GradingCategoryName::Assignment,
            GradingCategoryName::Quiz,
            GradingCategoryName::Exam,
            GradingCategoryName::Participation,
        );
});

it('rejects a scheme that does not total one hundred percent', function (): void {
    $fixture = GradingFixture::make();

    app(GradingSchemeService::class)->updateScheme($fixture->course, [
        'assignment' => 40,
        'quiz' => 20,
        'exam' => 20,
        'participation' => 0,
    ]);
})->throws(ValidationException::class);

it('updates valid weights and keeps the total at one hundred', function (): void {
    $fixture = GradingFixture::make();

    app(GradingSchemeService::class)->updateScheme($fixture->course, [
        'assignment' => 40,
        'quiz' => 25,
        'exam' => 30,
        'participation' => 5,
    ]);

    $scheme = app(GradingSchemeService::class);

    expect($scheme->totalWeight($fixture->course))->toBe(100.0)
        ->and((float) $scheme->categories($fixture->course)->firstWhere('name', GradingCategoryName::Participation)->weight)->toBe(5.0);
});

it('blocks publishing a course with an invalid scheme', function (): void {
    $fixture = LearningFixture::make();
    $fixture->course->gradingCategories()->update(['weight' => 10]);

    app(CourseService::class)->publish($fixture->course);
})->throws(ValidationException::class);

it('creates grade components when assignments and quizzes are published', function (): void {
    $fixture = GradingFixture::make();

    expect(GradeComponent::query()->count())->toBe(3)
        ->and($fixture->assignmentComponent->category->name)->toBe(GradingCategoryName::Assignment)
        ->and($fixture->quizComponent->category->name)->toBe(GradingCategoryName::Quiz)
        ->and($fixture->examComponent->category->name)->toBe(GradingCategoryName::Exam)
        ->and((float) $fixture->examComponent->max_score)->toBe(100.0);
});

it('does not create a component for an unpublished quiz', function (): void {
    $fixture = LearningFixture::make();

    $quiz = Quiz::factory()->create(['course_id' => $fixture->course->getKey()]);

    expect(GradeComponent::query()
        ->where('gradeable_type', $quiz->getMorphClass())
        ->where('gradeable_id', $quiz->getKey())
        ->exists())->toBeFalse();
});
