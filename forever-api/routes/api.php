<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORTACIONES DE CONTROLADORES ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FboController;
use App\Http\Controllers\Api\VentaController; 
use App\Http\Controllers\Api\ClienteController; // <-- NUEVO: Controlador de Clientes

use LaravelWebauthn\Http\Controllers\WebauthnKeyController;

/*
|--------------------------------------------------------------------------
| API Routes - Forever Living Bolivia
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// RUTAS PROTEGIDAS (Solo Admin / Usuarios Logueados)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user()->load('persona'); 
    });

    Route::post('/webauthn/keys/options', [WebauthnKeyController::class, 'create'])
        ->middleware(\App\Http\Middleware\WebauthnEmailMiddleware::class);
        
    Route::post('/webauthn/keys', [AuthController::class, 'register']);

    Route::post('/webauthn/get-token', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'token' => $user->createToken('biometric-login')->plainTextToken,
            'user' => $user
        ]);
    });

    Route::post('/user/toggle-biometrics', [AuthController::class, 'toggleBiometrics']);
    Route::post('/user/update-photo', [AuthController::class, 'updateProfile']);

    /**
     * MÓDULOS DE GESTIÓN FOREVER (Estructura Normalizada)
     */
    
    // Papelera de Productos
    Route::get('/products/trash', [ProductController::class, 'trash']); 
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']); 
    
    // Gestión de Productos
    Route::apiResource('products', ProductController::class);
    
    // Gestión de FBOs
    Route::apiResource('fbos', FboController::class);
    
    // Gestión de Ventas
    Route::apiResource('sales', VentaController::class);
    Route::post('/sales/calculate-taxes', [VentaController::class, 'calculateTaxes']);

    // ---> NUEVO: Gestión de Clientes Frecuentes <---
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});