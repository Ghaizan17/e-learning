<?php

namespace App\Models;

use App\Enums\LessonType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'chapter_id', 'title', 'slug', 'type', 'content_html', 'video_url', 'external_url',
    'code_language', 'estimated_duration_minutes', 'position', 'is_published',
])]
class Lesson extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LessonType::class,
            'position' => 'integer',
            'estimated_duration_minutes' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Chapter, $this>
     */
    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * @return HasMany<LessonAttachment, $this>
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(LessonAttachment::class);
    }

    /**
     * @return HasMany<LessonProgress, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    /**
     * @param  Builder<Lesson>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true)->whereHas('chapter', fn (Builder $chapter) => $chapter->where('is_published', true));
    }

    public function course(): Course
    {
        return $this->chapter->course;
    }

    public function isVisibleToStudents(): bool
    {
        return $this->is_published && (bool) $this->chapter?->is_published;
    }
}
