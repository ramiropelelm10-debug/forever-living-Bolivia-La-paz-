<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 1. Importación de tus controladores de gestión
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

// --- RUTAS PÚBLICAS ---

// Login inicial (Email y Password)
Route::post('/login', [AuthController::class, 'login']);

// Verificación de código OTP (2FA)
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

/**
 * Puente para Login Biométrico
 */
Route::post('/webauthn/get-token', function (Request $request) {
    /** @var \App\Models\User $user */
    $user = auth('sanctum')->user();
    
    if (!$user) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    return response()->json([
        'token' => $user->createToken('biometric-login')->plainTextToken,
        'user' => $user
    ]);
});


// --- RUTAS PROTEGIDAS (Sanctum) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Perfil del usuario
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    /**
     * REGISTRO DE HUELLA / FACE ID
     */
    Route::post('/webauthn/keys/options', [WebauthnKeyController::class, 'create'])
        ->middleware(\App\Http\Middleware\WebauthnEmailMiddleware::class);
        
    // USANDO TU CONTROLADOR PERSONALIZADO
    Route::post('/webauthn/keys', [AuthController::class, 'register']);

    // --- BIOMETRÍA Y PERFIL ---
    Route::post('/user/toggle-biometrics', [AuthController::class, 'toggleBiometrics']);
    
    // NUEVA RUTA: Para guardar la foto de la credencial capturada con la cámara
    Route::post('/user/update-photo', [AuthController::class, 'updateProfile']);

    // --- MÓDULOS DE GESTIÓN FOREVER ---
    
    // Gestión de Productos
    Route::apiResource('products', ProductController::class);
    
    // Gestión de FBOs
    Route::apiResource('fbos', FboController::class);
    
    // Gestión de Ventas
    Route::apiResource('sales', SaleController::class);
    
    // Cálculo de impuestos
    Route::post('/sales/calculate-taxes', [SaleController::class, 'calculateTaxes']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});