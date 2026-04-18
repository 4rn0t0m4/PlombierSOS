<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->plumbers()->count() === 0) {
            abort(403, 'Accès réservé aux professionnels.');
        }

        return $next($request);
    }
}
