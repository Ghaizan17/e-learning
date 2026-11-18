<?php

namespace App\Enums;

enum QuizAttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case Graded = 'graded';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'Dikerjakan',
            self::Submitted => 'Menunggu penilaian',
            self::Graded => 'Selesai dinilai',
            self::Expired => 'Waktu habis',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InProgress => 'info',
            self::Submitted => 'warning',
            self::Graded => 'success',
            self::Expired => 'danger',
        };
    }
}
