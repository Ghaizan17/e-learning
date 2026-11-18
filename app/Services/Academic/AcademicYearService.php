<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicYearService
{
    /**
     * Create an academic year. Only one year can be active at a time.
     *
     * @param  array{name: string, start_date: string, end_date: string, is_active?: bool}  $attributes
     */
    public function create(array $attributes): AcademicYear
    {
        return DB::transaction(fn (): AcademicYear => AcademicYear::query()->create($attributes));
    }

    /**
     * Update an academic year, keeping the semester dates inside its range.
     *
     * @param  array{name: string, start_date: string, end_date: string, is_active?: bool}  $attributes
     */
    public function update(AcademicYear $academicYear, array $attributes): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $attributes): AcademicYear {
            $academicYear->fill($attributes)->save();

            return $academicYear->refresh();
        });
    }

    /**
     * Activate a year and deactivate the previous one.
     */
    public function activate(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear): AcademicYear {
            $academicYear->update(['is_active' => true]);

            return $academicYear->refresh();
        });
    }

    /**
     * Deactivate a year. Semesters of that year are deactivated too.
     */
    public function deactivate(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear): AcademicYear {
            $academicYear->update(['is_active' => false]);
            $academicYear->semesters()->update(['is_active' => false]);

            return $academicYear->refresh();
        });
    }

    /**
     * First semester of a year, used as the default when creating assignments.
     */
    public function firstSemester(AcademicYear $academicYear): ?Semester
    {
        return $academicYear->semesters()->orderBy('sequence')->first();
    }

    /**
     * Ensure the given semester belongs to the academic year.
     */
    public function assertSemesterBelongsToYear(Semester $semester, AcademicYear $academicYear): void
    {
        if ($semester->academic_year_id !== $academicYear->getKey()) {
            throw ValidationException::withMessages([
                'semester_id' => 'Semester yang dipilih bukan milik tahun ajaran ini.',
            ]);
        }
    }
}
