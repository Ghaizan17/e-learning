<?php

namespace App\Services\Learning;

use App\Models\Chapter;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class ChapterService
{
    /**
     * @param  array{title: string, description?: ?string, is_published?: bool}  $data
     */
    public function create(Course $course, array $data): Chapter
    {
        return DB::transaction(fn (): Chapter => $course->chapters()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'position' => $this->nextPosition($course),
            'is_published' => (bool) ($data['is_published'] ?? false),
        ]));
    }

    /**
     * @param  array{title?: string, description?: ?string, is_published?: bool}  $data
     */
    public function update(Chapter $chapter, array $data): Chapter
    {
        $chapter->update($data);

        return $chapter->refresh();
    }

    public function publish(Chapter $chapter): Chapter
    {
        $chapter->update(['is_published' => true]);

        return $chapter->refresh();
    }

    /**
     * Unpublishing a chapter hides all of its lessons from students.
     */
    public function unpublish(Chapter $chapter): Chapter
    {
        $chapter->update(['is_published' => false]);

        return $chapter->refresh();
    }

    public function delete(Chapter $chapter): void
    {
        DB::transaction(fn () => $chapter->delete());
    }

    /**
     * Positions stay unique per course, including soft-deleted chapters.
     */
    public function nextPosition(Course $course): int
    {
        return (int) $course->chapters()->withTrashed()->max('position') + 1;
    }
}
