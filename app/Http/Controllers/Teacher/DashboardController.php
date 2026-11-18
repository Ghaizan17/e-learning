<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the teacher dashboard shell with the signed-in teacher's own data.
     */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('portal.teacher.dashboard', [
            'user' => $user,
            'isHomeroom' => $user->hasRole(RoleName::HomeroomTeacher->value),
        ]);
    }
}
