<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Services\Learning\LessonService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditLesson extends EditRecord
{
    protected static string $resource = LessonResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $service = app(LessonService::class);
        $desiredPublication = (bool) ($data['is_published'] ?? false);

        unset($data['is_published']);

        $lesson = $service->update($record, $data);

        try {
            if ($desiredPublication && ! $lesson->is_published) {
                $lesson = $service->publish($lesson);
            } elseif (! $desiredPublication && $lesson->is_published) {
                $lesson = $service->unpublish($lesson);
            }
        } catch (ValidationException $exception) {
            $this->addError('data.is_published', collect($exception->errors())->flatten()->first());
        }

        return $lesson;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
