<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Allow the request only when the authenticated user holds one of the given roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user !== null, 401);

        abort_unless(
            $user->is_active && $user->hasAnyRole($roles),
            403,
            'Anda tidak memiliki akses ke halaman ini.',
        );

        return $next($request);
    }
}
