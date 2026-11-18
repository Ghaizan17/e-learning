<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\RoleName;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan pengguna')
                    ->description('Data akun dan peran akses.')
                    ->icon(Heroicon::OutlinedUser)
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('phone')
                            ->label('Nomor telepon')
                            ->placeholder('-'),
                        TextEntry::make('roles.name')
                            ->label('Peran')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => RoleName::tryFrom($state)?->label() ?? $state)
                            ->placeholder('Belum ada peran'),
                        IconEntry::make('is_active')
                            ->label('Akun aktif')
                            ->boolean(),
                        TextEntry::make('email_verified_at')
                            ->label('Verifikasi email')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
