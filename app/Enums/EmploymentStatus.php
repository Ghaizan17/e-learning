<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Permanent = 'permanent';
    case Contract = 'contract';
    case Honorary = 'honorary';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Tetap',
            self::Contract => 'Kontrak',
            self::Honorary => 'Honorer',
        };
    }
}
