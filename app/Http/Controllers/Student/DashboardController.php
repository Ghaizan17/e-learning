<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the student dashboard shell with the signed-in student's own data.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('portal.student.dashboard', [
            'user' => $user,
        ]);
    }
}
