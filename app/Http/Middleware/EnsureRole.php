<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Allow only users whose role is in the allowed list.
     * Usage: ->middleware('role:admin,guru')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $allowed = array_filter(array_map('strtolower', array_map('trim', $roles)));
        $current = strtolower((string) ($user->role ?? ''));

        if (!empty($allowed) && !in_array($current, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
