<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        $middleware->statefulApi();

        // Cambiamos la sintaxis aquí para evitar el error de VS Code
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'login',
            'verify-otp',
            'webauthn/*',
            'api/webauthn/*',
        ]);

        // Si Authenticate::class te da error rojo, usa la ruta completa o bórralo, 
        // ya que Laravel 11 lo maneja internamente.
        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        
        // 4. Asegura que los errores siempre respondan en formato JSON
        // Así Vue puede leer los mensajes de error correctamente
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            return $request->expectsJson();
        });

    })->create();