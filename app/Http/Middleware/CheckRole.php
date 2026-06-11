<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Verifica que el usuario tenga el rol requerido
     * Uso en rutas: middleware('role:admin')
     *               middleware('role:admin,supervisor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        // Verificar que está autenticado
        if (!$user) {
            return response()->json([
                'message' => 'No autenticado'
            ], 401);
        }

        // Verificar que tiene uno de los roles permitidos
        if (!in_array($user->role, $roles)) {
            return response()->json([
                'message' => 'No tienes permiso para realizar esta acción'
            ], 403);
        }

        return $next($request);
    }
}