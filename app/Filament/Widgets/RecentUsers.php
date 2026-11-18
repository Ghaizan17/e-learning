<?php

namespace App\Filament\Widgets;

use App\Enums\RoleName;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentUsers extends TableWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pengguna terbaru')
            ->query(fn (): Builder => User::query()->with('roles')->latest()->limit(5))
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->description(fn (User $record): string => $record->email)
                    ->wrap()
                    ->grow(),
                TextColumn::make('roles.name')
                    ->label('Peran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RoleName::tryFrom($state)?->label() ?? $state)
                    ->color('indigo'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('Lihat')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record]))
                    ->iconButton()
                    ->tooltip('Buka pengguna'),
            ])
            ->paginated(false)
            ->emptyStateHeading('Belum ada pengguna');
    }
}
