<?php

namespace App\Filament\Resources\TeacherAssignments\Pages;

use App\Filament\Resources\TeacherAssignments\TeacherAssignmentResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTeacherAssignment extends ViewRecord
{
    protected static string $resource = TeacherAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
