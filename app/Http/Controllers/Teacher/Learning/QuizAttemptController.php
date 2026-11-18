<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use Illuminate\View\View;

class QuizAttemptController extends Controller
{
    public function show(QuizAttempt $attempt): View
    {
        $this->authorize('view', $attempt);

        $attempt->load([
            'student',
            'quiz.course.teacherAssignment.subject',
            'quiz.questions.options',
            'answers.selectedOption',
            'answers.selectedOptions',
        ]);

        return view('portal.teacher.quiz-attempts.show', [
            'attempt' => $attempt,
            'questions' => $attempt->quiz->questions,
            'answers' => $attempt->answers->keyBy('question_id'),
        ]);
    }
}
