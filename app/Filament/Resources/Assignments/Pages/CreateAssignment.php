<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Models\Course;
use App\Services\Learning\AssignmentService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateAssignment extends CreateRecord
{
    protected static string $resource = AssignmentResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(AssignmentService::class)->create(
            Course::query()->findOrFail($data['course_id']),
            $data,
        );
    }

    protected function getRedirectUrl(): string
    {
        return AssignmentResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
