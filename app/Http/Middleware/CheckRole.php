<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Verificar que el usuario tiene el rol requerido.
     * Uso: middleware('role:admin') o middleware('role:admin,resident')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'No tienes permiso para acceder a este recurso.',
            ], 403);
        }

        return $next($request);
    }
}
