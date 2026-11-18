<?php

namespace App\Filament\Resources\Grades\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Riwayat perubahan';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('old_score')->label('Skor lama')->placeholder('-'),
                TextColumn::make('new_score')->label('Skor baru'),
                TextColumn::make('editor.name')->label('Diubah oleh')->placeholder('-'),
                TextColumn::make('reason')->label('Alasan')->wrap()->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
