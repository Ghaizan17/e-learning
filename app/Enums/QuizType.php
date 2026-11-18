<?php

namespace App\Enums;

enum QuizType: string
{
    case Quiz = 'quiz';
    case Exam = 'exam';

    public function label(): string
    {
        return match ($this) {
            self::Quiz => 'Kuis',
            self::Exam => 'Ujian',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Quiz => 'info',
            self::Exam => 'danger',
        };
    }
}
