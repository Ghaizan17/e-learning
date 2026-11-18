<?php

namespace App\Http\Controllers\Teacher\Learning;

use App\Http\Controllers\Controller;
use App\Http\Requests\Learning\StoreCourseRequest;
use App\Http\Requests\Learning\UpdateCourseRequest;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\TeacherAssignment;
use App\Services\Learning\CourseService;
use App\Services\Learning\EnrollmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
        private readonly EnrollmentService $enrollments,
    ) {}

    public function index(Request $request): View
    {
        $courses = Course::query()
            ->whereHas('teacherAssignment', fn ($query) => $query->where('teacher_id', $request->user()->getKey()))
            ->with(['teacherAssignment.classroom', 'teacherAssignment.subject', 'teacherAssignment.semester'])
            ->withCount(['chapters', 'enrollments'])
            ->latest()
            ->paginate(12);

        return view('portal.teacher.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function create(Request $request): View
    {
        $assignments = TeacherAssignment::query()
            ->where('teacher_id', $request->user()->getKey())
            ->with(['classroom', 'subject', 'semester'])
            ->orderBy('classroom_id')
            ->get();

        return view('portal.teacher.courses.create', [
            'assignments' => $assignments,
            'selectedAssignment' => $request->integer('teacher_assignment_id') ?: null,
        ]);
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $assignment = TeacherAssignment::query()->findOrFail($request->validated('teacher_assignment_id'));

        abort_unless(
            $request->user()->isAdmin() || $assignment->teacher_id === $request->user()->getKey(),
            403,
        );

        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('course-thumbnails', 'public')
            : null;

        $course = $this->courses->create($assignment, [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'thumbnail_path' => $thumbnailPath,
        ]);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('status', 'Kursus dibuat sebagai draf. Tambahkan bab dan materi, lalu terbitkan.');
    }

    public function show(Request $request, Course $course): View
    {
        $this->authorize('view', $course);

        $course->load([
            'teacherAssignment.classroom',
            'teacherAssignment.subject',
            'teacherAssignment.semester',
            'chapters.lessons.attachments',
        ]);

        return view('portal.teacher.courses.show', [
            'course' => $course,
            'chapters' => $course->chapters,
            'enrollmentCount' => $course->enrollments()->count(),
            'studentCount' => $course->teacherAssignment?->classroom?->activeMembers()->count() ?? 0,
        ]);
    }

    public function edit(Request $request, Course $course): View
    {
        $this->authorize('update', $course);

        return view('portal.teacher.courses.edit', [
            'course' => $course->load('teacherAssignment.classroom', 'teacherAssignment.subject'),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $data = [
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
        ];

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail_path) {
                Storage::disk('public')->delete($course->thumbnail_path);
            }

            $data['thumbnail_path'] = $request->file('thumbnail')->store('course-thumbnails', 'public');
        } elseif ($request->boolean('remove_thumbnail') && $course->thumbnail_path) {
            Storage::disk('public')->delete($course->thumbnail_path);
            $data['thumbnail_path'] = null;
        }

        $this->courses->update($course, $data);

        return redirect()
            ->route('teacher.courses.show', $course)
            ->with('status', 'Detail kursus diperbarui.');
    }

    public function destroy(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $this->courses->delete($course);

        return redirect()
            ->route('teacher.courses.index')
            ->with('status', 'Kursus dihapus.');
    }

    public function publish(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('publish', $course);

        try {
            $this->courses->publish($course);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        return back()->with('status', 'Kursus diterbitkan dan siswa di kelas sudah terdaftar.');
    }

    public function archive(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('publish', $course);

        $this->courses->archive($course);

        return back()->with('status', 'Kursus diarsipkan.');
    }

    public function students(Request $request, Course $course): View
    {
        $this->authorize('manageEnrollments', $course);

        $enrollments = Enrollment::query()
            ->where('course_id', $course->getKey())
            ->with('user')
            ->orderBy('enrolled_at')
            ->get();

        $course->load('teacherAssignment.classroom');

        $activeMembers = $course->teacherAssignment?->classroom
            ?->activeMembers()
            ->with('student')
            ->get() ?? collect();

        return view('portal.teacher.courses.students', [
            'course' => $course,
            'enrollments' => $enrollments,
            'activeMembers' => $activeMembers,
        ]);
    }

    public function syncEnrollments(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $created = $this->enrollments->syncCourse($course);

        return back()->with('status', $created.' siswa baru didaftarkan ke kursus.');
    }
}
