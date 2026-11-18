<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use App\Models\TeacherAssignment;
use App\Services\Learning\CourseService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(CourseService::class)->create(
            TeacherAssignment::query()->findOrFail($data['teacher_assignment_id']),
            $data,
        );
    }
}
