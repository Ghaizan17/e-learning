<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\TeacherAssignment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * List the teaching assignments of the signed-in teacher.
     */
    public function index(Request $request): View
    {
        $assignments = TeacherAssignment::query()
            ->where('teacher_id', $request->user()->getKey())
            ->with([
                'classroom.department',
                'subject',
                'semester',
                'academicYear',
            ])
            ->withCount('courses')
            ->get();

        return view('portal.teacher.classes.index', [
            'assignments' => $assignments,
        ]);
    }
}
