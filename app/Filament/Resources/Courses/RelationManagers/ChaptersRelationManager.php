<?php

namespace App\Filament\Resources\Courses\RelationManagers;

use App\Models\Chapter;
use App\Services\Learning\ChapterService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    protected static ?string $title = 'Bab';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(150),
                Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(2)
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->label('Terbitkan bab')
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('position')
                    ->label('#')
                    ->alignCenter()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                TextColumn::make('lessons_count')
                    ->label('Materi')
                    ->counts('lessons')
                    ->alignCenter(),
                IconColumn::make('is_published')
                    ->label('Terbit')
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah bab')
                    ->mutateDataUsing(fn (array $data): array => $data + [
                        'position' => app(ChapterService::class)->nextPosition($this->getOwnerRecord()),
                        'is_published' => false,
                    ]),
            ])
            ->recordActions([
                Action::make('togglePublish')
                    ->label(fn (Chapter $record): string => $record->is_published ? 'Sembunyikan' : 'Terbitkan')
                    ->icon('heroicon-o-eye')
                    ->action(function (Chapter $record): void {
                        app(ChapterService::class)->update($record, ['is_published' => ! $record->is_published]);

                        Notification::make()->title('Status bab diperbarui')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }
}
