<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreLessonRequest;
use App\Http\Requests\Learning\UpdateLessonRequest;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Services\Learning\LessonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class LessonController extends Controller
{
    public function __construct(private readonly LessonService $lessons) {}

    public function store(StoreLessonRequest $request, Chapter $chapter): RedirectResponse
    {
        $this->lessons->create($chapter, $request->validated());

        return back()->with('status', 'Materi ditambahkan.');
    }

    public function update(UpdateLessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $this->lessons->update($lesson, $request->validated());

        return back()->with('status', 'Materi diperbarui.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->authorize('delete', $lesson);

        $this->lessons->delete($lesson);

        return back()->with('status', 'Materi dihapus.');
    }

    public function publish(Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        try {
            $this->lessons->publish($lesson);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Materi diterbitkan.');
    }

    public function unpublish(Lesson $lesson): RedirectResponse
    {
        $this->authorize('update', $lesson);

        $this->lessons->unpublish($lesson);

        return back()->with('status', 'Materi disembunyikan dari siswa.');
    }
}
