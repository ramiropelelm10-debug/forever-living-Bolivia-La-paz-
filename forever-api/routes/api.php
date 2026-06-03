<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORTACIONES DE CONTROLADORES ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FboController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\AdminController;

// 🔥 NUEVO: Controlador de PayPal
use App\Http\Controllers\Api\PaypalController;

use LaravelWebauthn\Http\Controllers\WebauthnKeyController;

/*
|--------------------------------------------------------------------------
| API Routes - Forever Living Bolivia
|--------------------------------------------------------------------------
*/

// ==========================================
// 🔓 RUTAS PÚBLICAS
// ==========================================
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register-request', [AuthController::class, 'registerRequest']);

// NUEVA RUTA PÚBLICA PARA EL ESCÁNER FACIAL
Route::post('/login-faceid', [AuthController::class, 'loginFaceId']);

// RUTAS DEL CATÁLOGO PÚBLICO
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// --- PASARELA DE PAGOS (PAYPAL) ---
// 🔥 PRUEBA: Movimos 'create' AQUÍ (Zona Pública) temporalmente
Route::post('/paypal/create', [PaypalController::class, 'createPayment']);
Route::get('/paypal/success', [PaypalController::class, 'capturePayment']);
Route::get('/paypal/cancel', [PaypalController::class, 'cancelPayment']);


// ==========================================
// 🔐 RUTAS PROTEGIDAS (Solo Admin / Usuarios Logueados con Sanctum)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // Obtener datos del usuario autenticado actual
    Route::get('/user', function (Request $request) {
        return $request->user()->load('persona');
    });

    // --- BIOMETRÍA (WEBAUTHN) ---
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

    // Gestión del perfil del usuario
    Route::post('/user/toggle-biometrics', [AuthController::class, 'toggleBiometrics']);
    Route::post('/user/update-photo', [AuthController::class, 'updateProfile']);

    /**
     * -----------------------------------------
     * MÓDULOS DE GESTIÓN FOREVER
     * -----------------------------------------
     */

    // Papelera de Productos
    Route::get('/trash/products', [ProductController::class, 'trash']);
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']);

    // Gestión de Productos y FBOs
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('fbos', FboController::class);

    // --- GESTIÓN DE VENTAS ---
    Route::get('/my-sales', [VentaController::class, 'mySales']);
    Route::apiResource('sales', VentaController::class);
    Route::post('/sales/calculate-taxes', [VentaController::class, 'calculateTaxes']);

    // Exportaciones
    Route::get('/sales/{id}/pdf', [VentaController::class, 'generarPdf']);
    Route::get('/sales/export/excel', [VentaController::class, 'exportExcel']);

    // Clientes
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);

    /**
     * -----------------------------------------
     * GESTIÓN DE ADMIN MASTER
     * -----------------------------------------
     */
    Route::prefix('admin')->group(function () {
        Route::get('/requests', [AdminController::class, 'pendingRequests']);
        Route::post('/users/{id}/respond', [AdminController::class, 'respond']);
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
        Route::post('/users/{id}/promote', [AdminController::class, 'promote']);
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});