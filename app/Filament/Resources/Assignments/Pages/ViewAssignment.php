<?php

namespace App\Filament\Resources\Assignments\Pages;

use App\Filament\Resources\Assignments\AssignmentResource;
use App\Services\Learning\AssignmentService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;

class ViewAssignment extends ViewRecord
{
    protected static string $resource = AssignmentResource::class;

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
                        app(AssignmentService::class)->publish($this->getRecord());
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Tugas belum bisa diterbitkan')
                            ->body(collect($exception->errors())->flatten()->first())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()->title('Tugas diterbitkan')->success()->send();
                }),
            EditAction::make(),
        ];
    }
}
