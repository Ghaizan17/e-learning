<?php

namespace App\Http\Controllers\Teacher\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\UpdateGradingSchemeRequest;
use App\Models\Course;
use App\Services\Grading\GradingSchemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradingSchemeController extends Controller
{
    public function __construct(private readonly GradingSchemeService $scheme) {}

    public function show(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        $this->scheme->ensureDefaultScheme($course);

        return view('portal.teacher.grading.scheme', [
            'course' => $course,
            'categories' => $this->scheme->categories($course),
            'totalWeight' => $this->scheme->totalWeight($course),
        ]);
    }

    public function update(UpdateGradingSchemeRequest $request, Course $course): RedirectResponse
    {
        $this->scheme->updateScheme($course, $request->validated('weights'));

        return redirect()
            ->route('teacher.grading.show', $course)
            ->with('status', 'Skema penilaian diperbarui.');
    }
}
