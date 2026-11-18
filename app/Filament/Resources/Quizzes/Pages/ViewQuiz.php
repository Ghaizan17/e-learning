<?php

namespace App\Filament\Resources\Quizzes\Pages;

use App\Filament\Resources\Quizzes\QuizResource;
use App\Services\Learning\QuizService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;

class ViewQuiz extends ViewRecord
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Terbitkan')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (): bool => ! $this->getRecord()->is_published)
                ->action(function (): void {
                    try {
                        app(QuizService::class)->publish($this->getRecord());
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Kuis belum bisa diterbitkan')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()->title('Kuis diterbitkan')->success()->send();
                }),
            EditAction::make(),
        ];
    }
}
