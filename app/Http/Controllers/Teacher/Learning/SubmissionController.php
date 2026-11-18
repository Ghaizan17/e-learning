<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\GradeSubmissionRequest;
use App\Models\Submission;
use App\Services\Learning\AssignmentSubmissionService;
use Illuminate\Http\RedirectResponse;

class SubmissionController extends Controller
{
    public function __construct(private readonly AssignmentSubmissionService $submissions) {}

    public function grade(GradeSubmissionRequest $request, Submission $submission): RedirectResponse
    {
        $this->submissions->grade(
            $submission,
            $request->user(),
            (float) $request->validated('score'),
            $request->validated('feedback'),
        );

        return back()->with('status', 'Nilai dan umpan balik disimpan.');
    }
}
