<?php

namespace App\Services\Academic;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AcademicRolloverService
{
    /**
     * Create a new academic year by copying the structure (not the members) of a previous year.
     *
     * @param  array{name: string, start_date: string, end_date: string}  $attributes
     */
    public function rollover(AcademicYear $source, array $attributes): AcademicYear
    {
        if (AcademicYear::query()->where('name', $attributes['name'])->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Tahun ajaran dengan nama tersebut sudah ada.',
            ]);
        }

        return DB::transaction(function () use ($source, $attributes): AcademicYear {
            $target = AcademicYear::query()->create($attributes + ['is_active' => false]);

            $offset = $source->start_date->diffInYears($target->start_date, absolute: false);

            $source->semesters()->orderBy('sequence')->get()->each(function (Semester $semester) use ($target, $offset): void {
                Semester::query()->create([
                    'academic_year_id' => $target->getKey(),
                    'name' => $semester->name,
                    'sequence' => $semester->sequence,
                    'start_date' => $semester->start_date->copy()->addYears($offset),
                    'end_date' => $semester->end_date->copy()->addYears($offset),
                    'is_active' => false,
                ]);
            });

            $source->classrooms()->orderBy('name')->get()->each(function (Classroom $classroom) use ($target): void {
                Classroom::query()->create([
                    'academic_year_id' => $target->getKey(),
                    'department_id' => $classroom->department_id,
                    'homeroom_teacher_id' => null,
                    'name' => $classroom->name,
                    'grade_level' => $classroom->grade_level,
                    'capacity' => $classroom->capacity,
                ]);
            });

            return $target->refresh();
        });
    }
}
