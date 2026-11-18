<?php

namespace App\Http\Controllers\Student\Learning;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\Learning\LessonProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LessonController extends Controller
{
    public function __construct(private readonly LessonProgressService $progress) {}

    public function show(Request $request, Course $course, Lesson $lesson): View
    {
        abort_unless($lesson->chapter?->course_id === $course->getKey(), 404);

        $this->authorize('view', $lesson);

        $user = $request->user();
        $course = $lesson->chapter->course;

        $progressRecord = $this->progress->start($user, $lesson);

        $sequence = $course->publishedLessons()->with('chapter')->get();
        $index = $sequence->search(fn (Lesson $item): bool => $item->getKey() === $lesson->getKey());

        return view('portal.student.lessons.show', [
            'lesson' => $lesson->load('attachments', 'chapter'),
            'course' => $course,
            'previous' => $index !== false && $index > 0 ? $sequence[$index - 1] : null,
            'next' => $index !== false && $index < $sequence->count() - 1 ? $sequence[$index + 1] : null,
            'progress' => $progressRecord,
            'percent' => $this->progress->percent($user, $course),
        ]);
    }

    public function complete(Request $request, Lesson $lesson): RedirectResponse
    {
        $this->authorize('view', $lesson);

        $this->progress->complete($request->user(), $lesson);

        return back()->with('status', 'Materi ditandai selesai.');
    }
}
