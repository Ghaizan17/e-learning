<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreAssignmentAttachmentRequest;
use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AssignmentAttachmentController extends Controller
{
    public function store(StoreAssignmentAttachmentRequest $request, Assignment $assignment): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store('assignment-attachments/'.$assignment->getKey(), 'local');

        $assignment->attachments()->create([
            'title' => $request->validated('title') ?: Str::limit($file->getClientOriginalName(), 140),
            'file_path' => $path,
            'disk' => 'local',
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('status', 'Lampiran tugas diunggah.');
    }

    public function destroy(AssignmentAttachment $attachment): RedirectResponse
    {
        $this->authorize('update', $attachment->assignment);

        $attachment->delete();

        return back()->with('status', 'Lampiran dihapus.');
    }
}
