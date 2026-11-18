<?php

namespace App\Filament\Resources\Classrooms\RelationManagers;

use App\Enums\ClassroomMemberStatus;
use App\Enums\RoleName;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use App\Services\Academic\ClassroomService;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\ValidationException;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Siswa';

    protected static ?string $modelLabel = 'anggota kelas';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Siswa')
                    ->options(fn (): array => User::query()
                        ->whereHas('roles', fn ($query) => $query->where('name', RoleName::Student->value))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->required()
                    ->disabledOn('edit'),
                DatePicker::make('joined_at')
                    ->label('Tanggal masuk')
                    ->default(today())
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required(),
                Textarea::make('note')
                    ->label('Catatan')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('student.name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('student.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('joined_at')
                    ->label('Masuk')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('left_at')
                    ->label('Keluar')
                    ->date('d M Y')
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (ClassroomMemberStatus $state): string => $state->label())
                    ->color(fn (ClassroomMemberStatus $state): string => $state->color()),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(ClassroomMemberStatus::cases())->mapWithKeys(
                        fn (ClassroomMemberStatus $status): array => [$status->value => $status->label()],
                    ))
                    ->default(ClassroomMemberStatus::Active->value),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah siswa')
                    ->using(function (array $data): ClassroomMember {
                        return app(ClassroomService::class)->enroll(
                            User::query()->findOrFail($data['user_id']),
                            $this->getOwnerRecord(),
                            isset($data['joined_at']) ? CarbonImmutable::parse($data['joined_at']) : null,
                        );
                    })
                    ->successNotificationTitle('Siswa ditambahkan ke kelas'),
                Action::make('transfer')
                    ->label('Pindah siswa')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('warning')
                    ->schema([
                        Select::make('student_id')
                            ->label('Siswa')
                            ->options(fn (): array => $this->getOwnerRecord()->activeMembers()
                                ->with('student')
                                ->get()
                                ->mapWithKeys(fn (ClassroomMember $member): array => [
                                    $member->user_id => $member->student?->name ?? 'Siswa #'.$member->user_id,
                                ])
                                ->all())
                            ->searchable()
                            ->required(),
                        Select::make('classroom_id')
                            ->label('Kelas tujuan')
                            ->options(fn (): array => Classroom::query()
                                ->where('academic_year_id', $this->getOwnerRecord()->academic_year_id)
                                ->whereKeyNot($this->getOwnerRecord()->getKey())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                        DatePicker::make('date')
                            ->label('Tanggal pindah')
                            ->default(today())
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        try {
                            app(ClassroomService::class)->transfer(
                                User::query()->findOrFail($data['student_id']),
                                $this->getOwnerRecord(),
                                Classroom::query()->findOrFail($data['classroom_id']),
                                CarbonImmutable::parse($data['date']),
                            );
                        } catch (ValidationException $exception) {
                            Notification::make()
                                ->title('Pemindahan gagal')
                                ->body(collect($exception->errors())->flatten()->first())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()->title('Siswa berhasil dipindahkan')->success()->send();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->schema(fn (Schema $schema): Schema => $schema->components([
                        DatePicker::make('joined_at')
                            ->label('Tanggal masuk')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])),
                Action::make('leave')
                    ->label('Keluarkan')
                    ->icon('heroicon-o-arrow-left-on-rectangle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Riwayat keanggotaan tetap tersimpan, hanya statusnya yang ditutup.')
                    ->visible(fn (ClassroomMember $record): bool => $record->isActive())
                    ->schema([
                        Select::make('status')
                            ->label('Alasan keluar')
                            ->options(collect(ClassroomMemberStatus::cases())
                                ->reject(fn (ClassroomMemberStatus $status): bool => $status === ClassroomMemberStatus::Active)
                                ->mapWithKeys(fn (ClassroomMemberStatus $status): array => [$status->value => $status->label()])
                                ->all())
                            ->default(ClassroomMemberStatus::Dropped->value)
                            ->required(),
                        DatePicker::make('left_at')
                            ->label('Tanggal keluar')
                            ->default(today())
                            ->native(false)
                            ->required(),
                    ])
                    ->action(function (ClassroomMember $record, array $data): void {
                        app(ClassroomService::class)->remove(
                            $record,
                            ClassroomMemberStatus::from($data['status']),
                            CarbonImmutable::parse($data['left_at']),
                        );

                        Notification::make()->title('Keanggotaan ditutup')->success()->send();
                    }),
            ]);
    }
}
