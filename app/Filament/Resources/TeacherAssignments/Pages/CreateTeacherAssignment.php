<?php

namespace App\Filament\Resources\TeacherAssignments\Pages;

use App\Filament\Resources\TeacherAssignments\TeacherAssignmentResource;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use App\Services\Academic\TeacherAssignmentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTeacherAssignment extends CreateRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TeacherAssignmentService::class)->assign(
            User::query()->findOrFail($data['teacher_id']),
            Classroom::query()->findOrFail($data['classroom_id']),
            Subject::query()->findOrFail($data['subject_id']),
            AcademicYear::query()->findOrFail($data['academic_year_id']),
            Semester::query()->findOrFail($data['semester_id']),
        );
    }
}
