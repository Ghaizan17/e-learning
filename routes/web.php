<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Learning\AssignmentAttachmentController as SharedAssignmentAttachmentController;
use App\Http\Controllers\Learning\LessonAttachmentController as SharedAttachmentController;
use App\Http\Controllers\Learning\SubmissionFileController as SharedSubmissionFileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\Learning\AssignmentController as StudentAssignmentController;
use App\Http\Controllers\Student\Learning\CourseController as StudentCourseController;
use App\Http\Controllers\Student\Learning\LessonController as StudentLessonController;
use App\Http\Controllers\Student\Learning\QuizAttemptController as StudentQuizAttemptController;
use App\Http\Controllers\Student\Learning\QuizController as StudentQuizController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\Learning\AssignmentAttachmentController;
use App\Http\Controllers\Teacher\Learning\AssignmentController as TeacherAssignmentController;
use App\Http\Controllers\Teacher\Learning\ChapterController;
use App\Http\Controllers\Teacher\Learning\CourseController;
use App\Http\Controllers\Teacher\Learning\LessonAttachmentController;
use App\Http\Controllers\Teacher\Learning\LessonController;
use App\Http\Controllers\Teacher\Learning\QuestionController as TeacherQuestionController;
use App\Http\Controllers\Teacher\Learning\QuizAnswerController as TeacherQuizAnswerController;
use App\Http\Controllers\Teacher\Learning\QuizAttemptController as TeacherQuizAttemptController;
use App\Http\Controllers\Teacher\Learning\QuizController as TeacherQuizController;
use App\Http\Controllers\Teacher\Learning\SubmissionController as TeacherSubmissionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'portal.defaults'])->group(function (): void {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('attachments/{attachment}/download', [SharedAttachmentController::class, 'download'])
        ->name('attachments.download');

    Route::get('assignment-attachments/{attachment}/download', [SharedAssignmentAttachmentController::class, 'download'])
        ->name('assignment-attachments.download');

    Route::get('submission-files/{file}/download', [SharedSubmissionFileController::class, 'download'])
        ->name('submission-files.download');
});

Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function (): void {
    Route::get('dashboard', StudentDashboardController::class)->name('dashboard');

    Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
    Route::get('courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
    Route::get('courses/{course}/lessons/{lesson}', [StudentLessonController::class, 'show'])->name('lessons.show');
    Route::post('lessons/{lesson}/complete', [StudentLessonController::class, 'complete'])->name('lessons.complete');

    Route::get('assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
    Route::post('assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');

    Route::get('quizzes', [StudentQuizController::class, 'index'])->name('quizzes.index');
    Route::get('quizzes/{quiz}', [StudentQuizController::class, 'show'])->name('quizzes.show');
    Route::post('quizzes/{quiz}/start', [StudentQuizController::class, 'start'])->name('quizzes.start');
    Route::get('quiz-attempts/{attempt}', [StudentQuizAttemptController::class, 'show'])->name('quiz-attempts.show');
    Route::post('quiz-attempts/{attempt}/answers', [StudentQuizAttemptController::class, 'saveAnswer'])->name('quiz-attempts.answers');
    Route::post('quiz-attempts/{attempt}/submit', [StudentQuizAttemptController::class, 'submit'])->name('quiz-attempts.submit');
    Route::get('quiz-attempts/{attempt}/result', [StudentQuizAttemptController::class, 'result'])->name('quiz-attempts.result');
});

Route::redirect('guru', '/guru/dashboard');
Route::redirect('walikelas', '/walikelas/dashboard');
Route::redirect('walikkelas', '/walikelas');

Route::middleware(['auth', 'role:teacher,homeroom_teacher', 'portal.prefix'])
    ->prefix('{portal}')
    ->whereIn('portal', ['guru', 'walikelas'])
    ->name('teacher.')
    ->group(function (): void {
        Route::get('dashboard', TeacherDashboardController::class)->name('dashboard');

        Route::get('classes', [TeacherClassController::class, 'index'])->name('classes.index');

        Route::get('courses', [CourseController::class, 'index'])->name('courses.index');
        Route::get('courses/create', [CourseController::class, 'create'])->name('courses.create');
        Route::post('courses', [CourseController::class, 'store'])->name('courses.store');
        Route::get('courses/{course}', [CourseController::class, 'show'])->name('courses.show');
        Route::get('courses/{course}/edit', [CourseController::class, 'edit'])->name('courses.edit');
        Route::patch('courses/{course}', [CourseController::class, 'update'])->name('courses.update');
        Route::delete('courses/{course}', [CourseController::class, 'destroy'])->name('courses.destroy');
        Route::post('courses/{course}/publish', [CourseController::class, 'publish'])->name('courses.publish');
        Route::post('courses/{course}/archive', [CourseController::class, 'archive'])->name('courses.archive');
        Route::get('courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
        Route::post('courses/{course}/sync-enrollments', [CourseController::class, 'syncEnrollments'])->name('courses.sync-enrollments');

        Route::post('courses/{course}/chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::patch('chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::delete('chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
        Route::post('chapters/{chapter}/publish', [ChapterController::class, 'publish'])->name('chapters.publish');
        Route::post('chapters/{chapter}/unpublish', [ChapterController::class, 'unpublish'])->name('chapters.unpublish');

        Route::post('chapters/{chapter}/lessons', [LessonController::class, 'store'])->name('lessons.store');
        Route::patch('lessons/{lesson}', [LessonController::class, 'update'])->name('lessons.update');
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])->name('lessons.destroy');
        Route::post('lessons/{lesson}/publish', [LessonController::class, 'publish'])->name('lessons.publish');
        Route::post('lessons/{lesson}/unpublish', [LessonController::class, 'unpublish'])->name('lessons.unpublish');

        Route::post('lessons/{lesson}/attachments', [LessonAttachmentController::class, 'store'])->name('lessons.attachments.store');
        Route::delete('attachments/{attachment}', [LessonAttachmentController::class, 'destroy'])->name('attachments.destroy');

        Route::get('assignments', [TeacherAssignmentController::class, 'index'])->name('assignments.index');
        Route::get('assignments/create', [TeacherAssignmentController::class, 'create'])->name('assignments.create');
        Route::post('assignments', [TeacherAssignmentController::class, 'store'])->name('assignments.store');
        Route::get('assignments/{assignment}', [TeacherAssignmentController::class, 'show'])->name('assignments.show');
        Route::get('assignments/{assignment}/edit', [TeacherAssignmentController::class, 'edit'])->name('assignments.edit');
        Route::patch('assignments/{assignment}', [TeacherAssignmentController::class, 'update'])->name('assignments.update');
        Route::delete('assignments/{assignment}', [TeacherAssignmentController::class, 'destroy'])->name('assignments.destroy');
        Route::post('assignments/{assignment}/publish', [TeacherAssignmentController::class, 'publish'])->name('assignments.publish');
        Route::post('assignments/{assignment}/unpublish', [TeacherAssignmentController::class, 'unpublish'])->name('assignments.unpublish');

        Route::post('assignments/{assignment}/attachments', [AssignmentAttachmentController::class, 'store'])->name('assignments.attachments.store');
        Route::delete('assignment-attachments/{attachment}', [AssignmentAttachmentController::class, 'destroy'])->name('assignment-attachments.destroy');

        Route::post('submissions/{submission}/grade', [TeacherSubmissionController::class, 'grade'])->name('submissions.grade');

        Route::get('quizzes', [TeacherQuizController::class, 'index'])->name('quizzes.index');
        Route::get('quizzes/create', [TeacherQuizController::class, 'create'])->name('quizzes.create');
        Route::post('quizzes', [TeacherQuizController::class, 'store'])->name('quizzes.store');
        Route::get('quizzes/{quiz}', [TeacherQuizController::class, 'show'])->name('quizzes.show');
        Route::get('quizzes/{quiz}/edit', [TeacherQuizController::class, 'edit'])->name('quizzes.edit');
        Route::patch('quizzes/{quiz}', [TeacherQuizController::class, 'update'])->name('quizzes.update');
        Route::delete('quizzes/{quiz}', [TeacherQuizController::class, 'destroy'])->name('quizzes.destroy');
        Route::post('quizzes/{quiz}/publish', [TeacherQuizController::class, 'publish'])->name('quizzes.publish');
        Route::post('quizzes/{quiz}/unpublish', [TeacherQuizController::class, 'unpublish'])->name('quizzes.unpublish');

        Route::post('quizzes/{quiz}/questions', [TeacherQuestionController::class, 'store'])->name('questions.store');
        Route::patch('questions/{question}', [TeacherQuestionController::class, 'update'])->name('questions.update');
        Route::delete('questions/{question}', [TeacherQuestionController::class, 'destroy'])->name('questions.destroy');

        Route::get('quiz-attempts/{attempt}', [TeacherQuizAttemptController::class, 'show'])->name('quiz-attempts.show');
        Route::post('quiz-answers/{answer}/grade', [TeacherQuizAnswerController::class, 'grade'])->name('quiz-answers.grade');
    });
