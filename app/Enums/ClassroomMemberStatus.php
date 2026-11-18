<?php

namespace App\Enums;

enum ClassroomMemberStatus: string
{
    case Active = 'active';
    case Transferred = 'transferred';
    case Graduated = 'graduated';
    case Dropped = 'dropped';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Transferred => 'Pindah kelas',
            self::Graduated => 'Lulus',
            self::Dropped => 'Keluar',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Transferred => 'warning',
            self::Graduated => 'info',
            self::Dropped => 'danger',
        };
    }
}
