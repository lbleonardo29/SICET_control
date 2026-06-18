<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Verificar si el usuario está autenticado
        if (!auth()->check()) {
            abort(403, 'No autorizado');
        }

        // Verificar si el rol del usuario coincide
        if (auth()->user()->role !== $role) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}