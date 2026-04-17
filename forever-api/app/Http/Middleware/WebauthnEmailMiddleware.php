<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WebauthnEmailMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Si el usuario está autenticado por Sanctum
        if (auth('sanctum')->check()) {
            $user = auth('sanctum')->user();
            
            // 1. Inyectamos en el cuerpo de la petición
            $request->merge([
                'email' => $user->email,
                'username' => $user->email,
            ]);

            // 2. Forzamos la inyección en el input (truco para Laravel 11)
            $request->request->add(['email' => $user->email]);
        }

        return $next($request);
    }
}