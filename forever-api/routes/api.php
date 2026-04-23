<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORTACIONES DE CONTROLADORES (ESTO ELIMINA LOS ERRORES) ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FboController;
use App\Http\Controllers\Api\SaleController;

// Controlador oficial de Biometría (WebAuthn)
use LaravelWebauthn\Http\Controllers\WebauthnKeyController;

/*
|--------------------------------------------------------------------------
| API Routes - Forever Living Bolivia
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS (Sin necesidad de estar logueado) ---

// Login tradicional
Route::post('/login', [AuthController::class, 'login']);

// Verificación de código OTP (Seguridad de 2 pasos)
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);


// --- RUTAS PROTEGIDAS (Requieren Token de Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Obtener los datos del usuario conectado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
     * SEGURIDAD BIOMÉTRICA (Llavero / Face ID)
     */
    // Generar opciones para registrar huella/rostro
    Route::post('/webauthn/keys/options', [WebauthnKeyController::class, 'create'])
        ->middleware(\App\Http\Middleware\WebauthnEmailMiddleware::class);
        
    // Registro final de la llave biométrica
    Route::post('/webauthn/keys', [AuthController::class, 'register']);

    // Puente para obtener token rápido mediante Biometría
    Route::post('/webauthn/get-token', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'token' => $user->createToken('biometric-login')->plainTextToken,
            'user' => $user
        ]);
    });

    // Ajustes de perfil y biometría
    Route::post('/user/toggle-biometrics', [AuthController::class, 'toggleBiometrics']);
    Route::post('/user/update-photo', [AuthController::class, 'updateProfile']);

    /**
     * MÓDULOS DE GESTIÓN FOREVER (CRUD)
     */
    
    // Gestión de Productos: Control de stock y precios
    Route::apiResource('products', ProductController::class);
    
    // Gestión de FBOs: Administra tus distribuidores y sus descuentos
    Route::apiResource('fbos', FboController::class);
    
    // GESTIÓN DE VENTAS: El motor que conecta tu tienda con PostgreSQL
    // Aquí es donde se procesa el botón "Procesar Venta" y se guardan las facturas
    Route::apiResource('sales', SaleController::class);
    
    // Cálculo previo de impuestos para Bolivia (13% IVA, 3% IT)
    Route::post('/sales/calculate-taxes', [SaleController::class, 'calculateTaxes']);

    // Logout: Elimina el token de acceso actual
    Route::post('/logout', [AuthController::class, 'logout']);
});