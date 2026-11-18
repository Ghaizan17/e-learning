<?php

namespace App\Services\Learning;

use App\Enums\LessonType;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Services\Content\HtmlSanitizer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LessonService
{
    public function __construct(private readonly HtmlSanitizer $sanitizer) {}

    /**
     * @param  array{title: string, type?: LessonType|string, content_html?: ?string, video_url?: ?string, external_url?: ?string, code_language?: ?string, estimated_duration_minutes?: ?int, is_published?: bool}  $data
     */
    public function create(Chapter $chapter, array $data): Lesson
    {
        return DB::transaction(fn (): Lesson => $chapter->lessons()->create([
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($chapter, $data['title']),
            'type' => $data['type'] ?? LessonType::Text,
            'content_html' => $this->sanitizer->sanitize($data['content_html'] ?? null),
            'video_url' => $data['video_url'] ?? null,
            'external_url' => $data['external_url'] ?? null,
            'code_language' => $data['code_language'] ?? null,
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'position' => $this->nextPosition($chapter),
            'is_published' => false,
        ]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Lesson $lesson, array $data): Lesson
    {
        if (array_key_exists('content_html', $data)) {
            $data['content_html'] = $this->sanitizer->sanitize($data['content_html']);
        }

        if (array_key_exists('title', $data) && $data['title'] !== $lesson->title) {
            $data['slug'] = $this->uniqueSlug($lesson->chapter, $data['title'], $lesson->getKey());
        }

        $lesson->update($data);

        return $lesson->refresh();
    }

    public function publish(Lesson $lesson): Lesson
    {
        if (! $lesson->chapter?->is_published) {
            throw ValidationException::withMessages([
                'is_published' => 'Terbitkan bab terlebih dahulu sebelum menerbitkan materi.',
            ]);
        }

        $lesson->update(['is_published' => true]);

        return $lesson->refresh();
    }

    public function unpublish(Lesson $lesson): Lesson
    {
        $lesson->update(['is_published' => false]);

        return $lesson->refresh();
    }

    public function delete(Lesson $lesson): void
    {
        DB::transaction(fn () => $lesson->delete());
    }

    public function nextPosition(Chapter $chapter): int
    {
        return (int) $chapter->lessons()->withTrashed()->max('position') + 1;
    }

    private function uniqueSlug(Chapter $chapter, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'materi';
        $slug = $base;
        $suffix = 2;

        while (Lesson::query()->withTrashed()
            ->where('chapter_id', $chapter->getKey())
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
