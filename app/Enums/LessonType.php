<?php

namespace App\Enums;

enum LessonType: string
{
    case Text = 'text';
    case Video = 'video';
    case Pdf = 'pdf';
    case Document = 'document';
    case Link = 'link';
    case Code = 'code';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Teks',
            self::Video => 'Video',
            self::Pdf => 'PDF',
            self::Document => 'Dokumen',
            self::Link => 'Tautan',
            self::Code => 'Kode',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Text => 'gray',
            self::Video => 'danger',
            self::Pdf => 'warning',
            self::Document => 'info',
            self::Link => 'primary',
            self::Code => 'success',
        };
    }

    /**
     * Whether the lesson renders rich text content.
     */
    public function usesContent(): bool
    {
        return in_array($this, [self::Text, self::Code], true);
    }
}
