<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Solo deja pasar a usuarios autenticados con rol 'admin'.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin') {
            return response()->json(['message' => 'No autorizado. Solo el administrador puede hacer esto.'], 403);
        }

        return $next($request);
    }
}