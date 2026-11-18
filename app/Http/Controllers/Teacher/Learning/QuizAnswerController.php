<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\GradeQuizAnswerRequest;
use App\Models\QuizAnswer;
use App\Services\Learning\QuizGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class QuizAnswerController extends Controller
{
    public function __construct(private readonly QuizGradingService $grading) {}

    public function grade(GradeQuizAnswerRequest $request, QuizAnswer $answer): RedirectResponse
    {
        try {
            $this->grading->gradeAnswer($answer, $request->user(), (float) $request->validated('score'));
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Nilai esai disimpan.');
    }
}
