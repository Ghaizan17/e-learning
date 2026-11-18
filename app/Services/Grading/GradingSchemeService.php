<?php

namespace App\Services\Grading;

use App\Enums\GradingCategoryName;
use App\Models\Course;
use App\Models\GradingCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GradingSchemeService
{
    /**
     * Default scheme totals 100. Participation has no source component yet, so its weight is 0.
     *
     * @var array<string, float>
     */
    public const DEFAULT_WEIGHTS = [
        'assignment' => 40,
        'quiz' => 30,
        'exam' => 30,
        'participation' => 0,
    ];

    /**
     * Create the four standard categories for a course without changing existing weights.
     */
    public function ensureDefaultScheme(Course $course): void
    {
        foreach (self::DEFAULT_WEIGHTS as $name => $weight) {
            GradingCategory::query()->firstOrCreate(
                ['course_id' => $course->getKey(), 'name' => $name],
                ['weight' => $weight],
            );
        }
    }

    /**
     * @return Collection<int, GradingCategory>
     */
    public function categories(Course $course): Collection
    {
        return $course->gradingCategories()
            ->get()
            ->sortBy(fn (GradingCategory $category): int => array_search(
                $category->name->value,
                array_column(GradingCategoryName::cases(), 'value'),
                true,
            ))
            ->values();
    }

    public function totalWeight(Course $course): float
    {
        return round((float) $course->gradingCategories()->sum('weight'), 2);
    }

    /**
     * @param  array<string, float|int|string>  $weights  keyed by category name
     */
    public function updateScheme(Course $course, array $weights): void
    {
        $this->ensureDefaultScheme($course);

        $normalized = [];

        foreach (GradingCategoryName::cases() as $category) {
            $value = $weights[$category->value] ?? $course->gradingCategories()
                ->where('name', $category->value)
                ->value('weight') ?? 0;

            if (! is_numeric($value) || (float) $value < 0 || (float) $value > 100) {
                throw ValidationException::withMessages([
                    'weights.'.$category->value => 'Bobot harus berupa angka antara 0 dan 100.',
                ]);
            }

            $normalized[$category->value] = round((float) $value, 2);
        }

        if (abs(array_sum($normalized) - 100) > 0.01) {
            throw ValidationException::withMessages([
                'weights' => 'Total bobot seluruh kategori harus tepat 100%.',
            ]);
        }

        DB::transaction(function () use ($course, $normalized): void {
            foreach ($normalized as $name => $weight) {
                $course->gradingCategories()
                    ->where('name', $name)
                    ->update(['weight' => $weight]);
            }
        });
    }

    /**
     * A course may only be published when its grading weights total 100.
     */
    public function assertValid(Course $course): void
    {
        $count = $course->gradingCategories()->count();

        if ($count === 0) {
            $this->ensureDefaultScheme($course);
        }

        if (abs($this->totalWeight($course) - 100) > 0.01) {
            throw ValidationException::withMessages([
                'grading' => 'Total bobot komponen penilaian harus tepat 100% sebelum kursus diterbitkan.',
            ]);
        }
    }
}
