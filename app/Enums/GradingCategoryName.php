<?php

namespace App\Enums;

enum GradingCategoryName: string
{
    case Assignment = 'assignment';
    case Quiz = 'quiz';
    case Exam = 'exam';
    case Participation = 'participation';

    public function label(): string
    {
        return match ($this) {
            self::Assignment => 'Tugas',
            self::Quiz => 'Kuis',
            self::Exam => 'Ujian',
            self::Participation => 'Partisipasi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Assignment => 'info',
            self::Quiz => 'success',
            self::Exam => 'danger',
            self::Participation => 'warning',
        };
    }
}
