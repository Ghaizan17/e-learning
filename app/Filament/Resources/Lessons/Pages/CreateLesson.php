<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Resources\Lessons\LessonResource;
use App\Models\Chapter;
use App\Services\Learning\LessonService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLesson extends CreateRecord
{
    protected static string $resource = LessonResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $chapter = Chapter::query()->findOrFail($data['chapter_id']);

        $lesson = app(LessonService::class)->create($chapter, $data);

        if ((bool) ($data['is_published'] ?? false)) {
            app(LessonService::class)->publish($lesson);
        }

        return $lesson;
    }
}
