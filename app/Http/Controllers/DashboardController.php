<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Send the authenticated user to the portal that matches their role.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->to($request->user()->homeUrl());
    }
}
