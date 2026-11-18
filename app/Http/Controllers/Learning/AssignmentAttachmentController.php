<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Models\AssignmentAttachment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssignmentAttachmentController extends Controller
{
    /**
     * Stream a private assignment attachment to an authorised user.
     */
    public function download(AssignmentAttachment $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        $disk = Storage::disk($attachment->disk);

        abort_unless($disk->exists($attachment->file_path), 404);

        $extension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
        $filename = Str::slug($attachment->title) ?: 'lampiran-tugas';

        return $disk->download($attachment->file_path, $filename.($extension !== '' ? '.'.$extension : ''));
    }
}
