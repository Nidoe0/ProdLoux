<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Non authentifié.'], 401)
                : redirect()->route('login');
        }

        if (!in_array($request->user()->role, $roles)) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Accès refusé. Rôle requis : ' . implode(' ou ', $roles)], 403)
                : abort(403);
        }

        return $next($request);
    }
}
