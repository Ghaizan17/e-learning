<?php

namespace App\Enums;

enum GradeLevel: int
{
    case Ten = 10;
    case Eleven = 11;
    case Twelve = 12;

    public function label(): string
    {
        return match ($this) {
            self::Ten => 'Kelas X',
            self::Eleven => 'Kelas XI',
            self::Twelve => 'Kelas XII',
        };
    }
}
