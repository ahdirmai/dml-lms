<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckActiveRole
{
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = $request->user();
        if ($user && $user->active_role === $role) {
            return $next($request);
        }
        abort(403, 'Akses ditolak untuk role ini.');
    }
}
