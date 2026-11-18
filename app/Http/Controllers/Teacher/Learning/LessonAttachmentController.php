<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreLessonAttachmentRequest;
use App\Models\Lesson;
use App\Models\LessonAttachment;
use Illuminate\Http\RedirectResponse;

class LessonAttachmentController extends Controller
{
    public function store(StoreLessonAttachmentRequest $request, Lesson $lesson): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('lesson-attachments', 'local');

        $lesson->attachments()->create([
            'title' => $request->validated('title') ?: $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('status', 'Lampiran diunggah.');
    }

    public function destroy(LessonAttachment $attachment): RedirectResponse
    {
        $this->authorize('delete', $attachment);

        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.');
    }
}
