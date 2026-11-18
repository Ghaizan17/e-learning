<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Models\Course;
use App\Services\Learning\QuizService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateQuiz extends CreateRecord
{
    protected static string $resource = QuizResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(QuizService::class)->create(
            Course::query()->findOrFail($data['course_id']),
            $data,
        );
    }

    protected function getRedirectUrl(): string
    {
        return QuizResource::getUrl('view', ['record' => $this->getRecord()]);
    }
}
