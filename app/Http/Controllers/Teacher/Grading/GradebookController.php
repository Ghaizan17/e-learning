<?php

namespace App\Http\Controllers\Teacher\Grading;

use App\Http\Controllers\Controller;
use App\Http\Requests\Grading\UpdateGradeRequest;
use App\Models\Course;
use App\Models\Grade;
use App\Services\Grading\GradeAuditService;
use App\Services\Grading\GradingSchemeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradebookController extends Controller
{
    public function __construct(
        private readonly GradingSchemeService $scheme,
        private readonly GradeAuditService $audit,
    ) {}

    public function index(Request $request): View
    {
        $courses = Course::query()
            ->whereHas('teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->with(['teacherAssignment.subject', 'teacherAssignment.classroom'])
            ->orderBy('title')
            ->get();

        return view('portal.teacher.grading.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        $this->scheme->ensureDefaultScheme($course);

        $components = $course->gradeComponents()
            ->with('category')
            ->orderBy('grading_category_id')
            ->orderBy('title')
            ->get();

        $enrollments = $course->enrollments()
            ->active()
            ->with('user')
            ->get()
            ->sortBy(fn ($enrollment): string => (string) $enrollment->user?->name)
            ->values();

        $grades = Grade::query()
            ->where('course_id', $course->getKey())
            ->get()
            ->keyBy(fn (Grade $grade): string => $grade->user_id.'-'.$grade->grade_component_id);

        return view('portal.teacher.grading.show', [
            'course' => $course,
            'categories' => $this->scheme->categories($course),
            'components' => $components,
            'enrollments' => $enrollments,
            'grades' => $grades,
        ]);
    }

    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $this->audit->updateGrade($grade, $request->user(), $request->validated());

        return back()->with('status', 'Nilai diperbarui dan tercatat pada riwayat.');
    }

    public function publish(Request $request, Grade $grade): RedirectResponse
    {
        $this->authorize('publish', $grade);

        $data = $request->validate(['is_published' => ['required', 'boolean']]);

        $this->audit->setPublished($grade, $request->user(), (bool) $data['is_published']);

        return back()->with('status', $data['is_published'] ? 'Nilai dipublikasikan.' : 'Nilai disembunyikan.');
    }

    public function export(Request $request, Course $course): StreamedResponse
    {
        $this->authorize('update', $course);

        $components = $course->gradeComponents()->with('category')->orderBy('id')->get();
        $enrollments = $course->enrollments()->active()->with('user')->get();
        $grades = Grade::query()->where('course_id', $course->getKey())->get()
            ->keyBy(fn (Grade $grade): string => $grade->user_id.'-'.$grade->grade_component_id);

        $lines = [];
        $header = ['Nama', 'Email', 'Nilai Akhir', 'Huruf'];
        foreach ($components as $component) {
            $header[] = $component->category->name->label().': '.$component->title;
        }
        $lines[] = $header;

        foreach ($enrollments as $enrollment) {
            $row = [
                $enrollment->user?->name,
                $enrollment->user?->email,
                $enrollment->final_score,
                $enrollment->letter_grade,
            ];

            foreach ($components as $component) {
                $row[] = $grades->get($enrollment->user_id.'-'.$component->getKey())?->score;
            }

            $lines[] = $row;
        }

        $callback = function () use ($lines): void {
            $handle = fopen('php://output', 'w');
            foreach ($lines as $line) {
                fputcsv($handle, $line);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="nilai-'.$course->slug.'.csv"',
        ]);
    }
}
