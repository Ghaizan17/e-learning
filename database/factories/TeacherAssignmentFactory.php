<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherAssignment>
 */
class TeacherAssignmentFactory extends Factory
{
    protected $model = TeacherAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory()->teacher(),
            'classroom_id' => Classroom::factory(),
            'subject_id' => Subject::factory(),
            'academic_year_id' => fn (array $attributes): int => (int) Classroom::query()
                ->findOrFail($attributes['classroom_id'])
                ->academic_year_id,
            'semester_id' => fn (array $attributes): int => Semester::factory()
                ->create(['academic_year_id' => $attributes['academic_year_id']])
                ->getKey(),
        ];
    }
}
