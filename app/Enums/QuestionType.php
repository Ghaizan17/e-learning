<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case MultipleSelect = 'multiple_select';
    case TrueFalse = 'true_false';
    case ShortAnswer = 'short_answer';
    case Essay = 'essay';

    public function label(): string
    {
        return match ($this) {
            self::MultipleChoice => 'Pilihan ganda',
            self::MultipleSelect => 'Pilihan ganda (multi jawaban)',
            self::TrueFalse => 'Benar / Salah',
            self::ShortAnswer => 'Jawaban singkat',
            self::Essay => 'Esai',
        };
    }

    public function isAutoGraded(): bool
    {
        return $this !== self::Essay;
    }

    public function requiresOptions(): bool
    {
        return $this !== self::Essay;
    }

    public function allowsMultipleSelection(): bool
    {
        return $this === self::MultipleSelect;
    }
}
