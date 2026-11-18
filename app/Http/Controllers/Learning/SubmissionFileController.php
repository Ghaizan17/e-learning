<?php

namespace App\Http\Controllers\Learning;

use App\Http\Controllers\Controller;
use App\Models\SubmissionFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    /**
     * Stream a private submission file to its owner or a grading teacher.
     */
    public function download(SubmissionFile $file): StreamedResponse
    {
        $this->authorize('download', $file);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($file->file_path), 404);

        $extension = pathinfo($file->original_name, PATHINFO_EXTENSION);
        $filename = Str::slug(pathinfo($file->original_name, PATHINFO_FILENAME)) ?: 'berkas-tugas';

        return $disk->download($file->file_path, $filename.($extension !== '' ? '.'.$extension : ''));
    }
}
