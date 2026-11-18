<?php

namespace Database\Factories;

use App\Enums\SemesterName;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Semester>
 */
class SemesterFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $year = AcademicYear::factory();

        return [
            'academic_year_id' => $year,
            'name' => SemesterName::Odd,
            'sequence' => SemesterName::Odd->sequence(),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->startOfYear()->addMonths(5)->toDateString(),
            'is_active' => false,
        ];
    }

    public function forYear(AcademicYear $academicYear): static
    {
        return $this->state(fn (array $attributes): array => [
            'academic_year_id' => $academicYear->getKey(),
        ]);
    }

    public function odd(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => SemesterName::Odd,
            'sequence' => SemesterName::Odd->sequence(),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->startOfYear()->addMonths(5)->toDateString(),
        ]);
    }

    public function even(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => SemesterName::Even,
            'sequence' => SemesterName::Even->sequence(),
            'start_date' => now()->startOfYear()->addMonths(6)->toDateString(),
            'end_date' => now()->startOfYear()->addMonths(11)->toDateString(),
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => ['is_active' => true]);
    }
}
