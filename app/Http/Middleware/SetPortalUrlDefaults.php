<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes teacher portal route names resolvable on shared pages (profile, notifications).
 */
class SetPortalUrlDefaults
{
    public function handle(Request $request, Closure $next): Response
    {
        $prefix = $request->user()?->teacherPortalPrefix();

        if ($prefix !== null) {
            URL::defaults(['portal' => $prefix]);
        }

        return $next($request);
    }
}
