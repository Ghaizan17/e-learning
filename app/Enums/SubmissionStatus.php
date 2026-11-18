<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Late = 'late';
    case Graded = 'graded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dikumpulkan',
            self::Late => 'Terlambat',
            self::Graded => 'Dinilai',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'info',
            self::Late => 'warning',
            self::Graded => 'success',
        };
    }
}
