<?php

namespace App\Enums;

enum SemesterName: string
{
    case Odd = 'odd';
    case Even = 'even';

    public function label(): string
    {
        return match ($this) {
            self::Odd => 'Ganjil',
            self::Even => 'Genap',
        };
    }

    public function sequence(): int
    {
        return match ($this) {
            self::Odd => 1,
            self::Even => 2,
        };
    }
}
