<?php

namespace App\Services\Academic;

use App\Enums\RoleName;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeacherAssignmentService
{
    public function __construct(private readonly AcademicYearService $academicYears) {}

    /**
     * Assign a teacher to a subject in a classroom for one semester.
     */
    public function assign(
        User $teacher,
        Classroom $classroom,
        Subject $subject,
        AcademicYear $academicYear,
        Semester $semester,
    ): TeacherAssignment {
        if (! $teacher->hasAnyRole(RoleName::teacherValues())) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Pengguna yang dipilih bukan guru.',
            ]);
        }

        if ($classroom->academic_year_id !== $academicYear->getKey()) {
            throw ValidationException::withMessages([
                'classroom_id' => 'Kelas yang dipilih bukan milik tahun ajaran ini.',
            ]);
        }

        $this->academicYears->assertSemesterBelongsToYear($semester, $academicYear);

        return DB::transaction(function () use ($teacher, $classroom, $subject, $academicYear, $semester): TeacherAssignment {
            $exists = TeacherAssignment::query()
                ->where('teacher_id', $teacher->getKey())
                ->where('classroom_id', $classroom->getKey())
                ->where('subject_id', $subject->getKey())
                ->where('semester_id', $semester->getKey())
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Penugasan guru ini sudah ada untuk kelas, mata pelajaran, dan semester tersebut.',
                ]);
            }

            return TeacherAssignment::query()->create([
                'teacher_id' => $teacher->getKey(),
                'classroom_id' => $classroom->getKey(),
                'subject_id' => $subject->getKey(),
                'academic_year_id' => $academicYear->getKey(),
                'semester_id' => $semester->getKey(),
            ]);
        });
    }

    /**
     * Update an assignment and keep the classroom/academic-year/semester consistent.
     *
     * @param  array{teacher_id: int, classroom_id: int, subject_id: int, academic_year_id: int, semester_id: int}  $attributes
     */
    public function update(TeacherAssignment $assignment, array $attributes): TeacherAssignment
    {
        $teacher = User::query()->findOrFail($attributes['teacher_id']);

        if (! $teacher->hasAnyRole(RoleName::teacherValues())) {
            throw ValidationException::withMessages([
                'teacher_id' => 'Pengguna yang dipilih bukan guru.',
            ]);
        }

        return DB::transaction(function () use ($assignment, $attributes): TeacherAssignment {
            $classroom = Classroom::query()->findOrFail($attributes['classroom_id']);
            $semester = Semester::query()->findOrFail($attributes['semester_id']);
            $academicYear = AcademicYear::query()->findOrFail($attributes['academic_year_id']);

            if ($classroom->academic_year_id !== $academicYear->getKey()) {
                throw ValidationException::withMessages([
                    'classroom_id' => 'Kelas yang dipilih bukan milik tahun ajaran ini.',
                ]);
            }

            $this->academicYears->assertSemesterBelongsToYear($semester, $academicYear);

            $duplicate = TeacherAssignment::query()
                ->whereKeyNot($assignment->getKey())
                ->where('teacher_id', $attributes['teacher_id'])
                ->where('classroom_id', $attributes['classroom_id'])
                ->where('subject_id', $attributes['subject_id'])
                ->where('semester_id', $attributes['semester_id'])
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'teacher_id' => 'Penugasan guru ini sudah ada untuk kelas, mata pelajaran, dan semester tersebut.',
                ]);
            }

            $assignment->update($attributes);

            return $assignment->refresh();
        });
    }

    /**
     * Remove an assignment. Guarded in Phase 2 once courses exist.
     */
    public function delete(TeacherAssignment $assignment): void
    {
        DB::transaction(fn () => $assignment->delete());
    }
}
