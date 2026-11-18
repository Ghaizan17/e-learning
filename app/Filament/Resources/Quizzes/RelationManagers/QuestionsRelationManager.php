<?php

namespace App\Filament\Resources\Quizzes\RelationManagers;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Services\Learning\QuestionService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Soal';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('type')
                    ->label('Tipe')
                    ->options(collect(QuestionType::cases())->mapWithKeys(
                        fn (QuestionType $type): array => [$type->value => $type->label()],
                    ))
                    ->required()
                    ->default('multiple_choice'),
                Textarea::make('question_text')
                    ->label('Pertanyaan')
                    ->rows(3)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('score')
                    ->label('Poin')
                    ->numeric()
                    ->minValue(0.5)
                    ->maxValue(100)
                    ->required()
                    ->default(10),
                Textarea::make('explanation')
                    ->label('Penjelasan')
                    ->rows(2)
                    ->columnSpanFull(),
                Repeater::make('options')
                    ->label('Pilihan jawaban')
                    ->hiddenOn('edit')
                    ->schema([
                        TextInput::make('option_text')->label('Teks')->required(),
                        Toggle::make('is_correct')->label('Jawaban benar'),
                    ])
                    ->columns(1)
                    ->defaultItems(2)
                    ->maxItems(10)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question_text')
            ->columns([
                TextColumn::make('position')->label('#')->sortable()->alignCenter(),
                TextColumn::make('type')->label('Tipe')->badge(),
                TextColumn::make('question_text')->label('Pertanyaan')->limit(70),
                TextColumn::make('score')->label('Poin')->alignCenter(),
                TextColumn::make('options_count')->label('Pilihan')->counts('options')->alignCenter(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->using(function (array $data): Question {
                        try {
                            return app(QuestionService::class)->create($this->getOwnerRecord(), $data);
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Soal belum bisa disimpan')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            throw $exception;
                        }
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->defaultSort('position');
    }
}
