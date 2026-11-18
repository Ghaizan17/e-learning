<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academic_year_id' => AcademicYear::factory(),
            'department_id' => Department::factory(),
            'homeroom_teacher_id' => null,
            'name' => 'Kelas '.fake()->unique()->bothify('###-???'),
            'grade_level' => fake()->randomElement([
                GradeLevel::Ten,
                GradeLevel::Eleven,
                GradeLevel::Twelve,
            ]),
            'capacity' => 32,
        ];
    }

    public function forYear(AcademicYear $academicYear): static
    {
        return $this->state(fn (array $attributes): array => [
            'academic_year_id' => $academicYear->getKey(),
        ]);
    }
}
