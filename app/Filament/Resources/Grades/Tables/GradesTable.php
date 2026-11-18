<?php

namespace App\Filament\Resources\Grades\Tables;

use App\Models\Grade;
use App\Services\Grading\GradeAuditService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.name')->label('Siswa')->searchable()->sortable(),
                TextColumn::make('course.title')->label('Kursus')->searchable(),
                TextColumn::make('component.title')->grow()->label('Komponen')->limit(30),
                TextColumn::make('component.category.name')->label('Kategori')->badge(),
                TextColumn::make('score')->label('Skor')->alignCenter(),
                TextColumn::make('max_score')->label('Maks')->alignCenter(),
                TextColumn::make('percentage')->label('%')->suffix('%')->alignCenter(),
                IconColumn::make('is_published')->label('Terbit')->boolean(),
            ])
            ->filters([
                SelectFilter::make('course_id')->label('Kursus')->relationship('course', 'title'),
            ])
            ->recordActions([
                Action::make('editScore')
                    ->label('Ubah nilai')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Ubah nilai')
                    ->schema([
                        TextInput::make('score')->label('Skor')->numeric()->required(),
                        Textarea::make('reason')->label('Alasan perubahan')->rows(2)->required(),
                        Toggle::make('is_published')->label('Tampilkan ke siswa'),
                    ])
                    ->fillForm(fn (Grade $record): array => [
                        'score' => $record->score,
                        'is_published' => $record->is_published,
                    ])
                    ->action(function (Grade $record, array $data): void {
                        app(GradeAuditService::class)->updateGrade($record, auth()->user(), $data);

                        Notification::make()->title('Nilai diperbarui')->success()->send();
                    }),
                ViewAction::make(),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
