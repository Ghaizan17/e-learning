<?php

namespace App\Filament\Resources\Assignments\RelationManagers;

use App\Models\Submission;
use App\Services\Learning\AssignmentSubmissionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class SubmissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'submissions';

    protected static ?string $title = 'Pengumpulan';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Siswa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('attempt_no')
                    ->label('Percobaan')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('submitted_at')
                    ->label('Dikumpulkan')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
                TextColumn::make('grade.score')
                    ->label('Nilai')
                    ->placeholder('-')
                    ->alignCenter(),
                TextColumn::make('files_count')
                    ->label('Berkas')
                    ->counts('files')
                    ->alignCenter(),
            ])
            ->recordActions([
                Action::make('grade')
                    ->label(fn (Submission $record): string => $record->grade ? 'Ubah nilai' : 'Beri nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->schema(fn (Submission $record): array => [
                        TextInput::make('score')
                            ->label('Skor')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue((float) $record->assignment->max_score)
                            ->required(),
                        Textarea::make('feedback')
                            ->label('Umpan balik')
                            ->rows(3),
                    ])
                    ->fillForm(fn (Submission $record): array => [
                        'score' => $record->grade?->score,
                        'feedback' => $record->grade?->feedback,
                    ])
                    ->action(function (Submission $record, array $data): void {
                        try {
                            app(AssignmentSubmissionService::class)->grade(
                                $record,
                                auth()->user(),
                                (float) $data['score'],
                                $data['feedback'] ?? null,
                            );
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Nilai gagal disimpan')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Nilai disimpan')->success()->send();
                    }),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
