<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffOnly
{
    /**
     * Deja pasar solo al personal del panel: admin o inventario.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['admin', 'inventario'], true)) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        return $next($request);
    }
}