<?php

namespace App\Models;

use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['title', 'cover_image_path', 'content'])]
class Material extends Model implements HasRichContent
{
    use HasFactory;
    use InteractsWithRichContent;
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }

    public function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->json()
            ->fileAttachmentsDisk('public')
            ->fileAttachmentsVisibility('public');
    }
}
