<?php

namespace App\Filament\Resources\TeacherAssignments\Pages;

use App\Filament\Resources\TeacherAssignments\TeacherAssignmentResource;
use App\Services\Academic\TeacherAssignmentService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTeacherAssignment extends EditRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TeacherAssignmentService::class)->update($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
