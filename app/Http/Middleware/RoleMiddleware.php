<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        foreach ($roles as $role) {
            if ($user->hasRole(trim($role))) {
                return $next($request);
            }
        }

        if ($user->hasRole('cashier')) {
            return redirect()->route('admin.pos');
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
