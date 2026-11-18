<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreChapterRequest;
use App\Http\Requests\Learning\UpdateChapterRequest;
use App\Models\Chapter;
use App\Models\Course;
use App\Services\Learning\ChapterService;
use Illuminate\Http\RedirectResponse;

class ChapterController extends Controller
{
    public function __construct(private readonly ChapterService $chapters) {}

    public function store(StoreChapterRequest $request, Course $course): RedirectResponse
    {
        $this->chapters->create($course, $request->validated());

        return back()->with('status', 'Bab ditambahkan.');
    }

    public function update(UpdateChapterRequest $request, Chapter $chapter): RedirectResponse
    {
        $this->chapters->update($chapter, $request->validated());

        return back()->with('status', 'Bab diperbarui.');
    }

    public function destroy(Chapter $chapter): RedirectResponse
    {
        $this->authorize('delete', $chapter);

        $this->chapters->delete($chapter);

        return back()->with('status', 'Bab dihapus.');
    }

    public function publish(Chapter $chapter): RedirectResponse
    {
        $this->authorize('update', $chapter);

        $this->chapters->publish($chapter);

        return back()->with('status', 'Bab diterbitkan.');
    }

    public function unpublish(Chapter $chapter): RedirectResponse
    {
        $this->authorize('update', $chapter);

        $this->chapters->unpublish($chapter);

        return back()->with('status', 'Bab disembunyikan dari siswa.');
    }
}
