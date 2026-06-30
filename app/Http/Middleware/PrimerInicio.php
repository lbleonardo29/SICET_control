<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PrimerInicio
{
    /**
     * Mientras el usuario tenga primer_inicio = 1 debe completar el alta
     * (firma + nueva contraseña) desde el modal del dashboard. Se le permite
     * únicamente ver el dashboard, enviar el formulario de alta y cerrar sesión.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->necesitaAlta()) {
            $permitidas = ['dashboard', 'cambiar.password', 'logout'];

            if (!in_array($request->route()?->getName(), $permitidas, true)) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
