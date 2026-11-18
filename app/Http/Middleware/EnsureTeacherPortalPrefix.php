<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the teacher portal URL in sync with the signed-in role:
 * teacher => /guru, homeroom teacher => /walikelas.
 */
class EnsureTeacherPortalPrefix
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedPrefix = $request->user()?->isHomeroomTeacher() ? 'walikelas' : 'guru';

        abort_unless($request->route('portal') === $expectedPrefix, 404);

        URL::defaults(['portal' => $expectedPrefix]);

        // The prefix only selects the portal; hide it from controller arguments.
        $request->route()?->forgetParameter('portal');

        return $next($request);
    }
}
