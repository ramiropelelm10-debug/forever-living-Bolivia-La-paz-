<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Importa los controladores necesarios para gestionar las diferentes funciones del sistema.
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\FboController;
use App\Http\Controllers\Api\VentaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PaypalController;

// Define las rutas públicas principales que no requieren autenticación, facilitando el inicio de sesión tradicional y el registro inicial.
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register-request', [AuthController::class, 'registerRequest']);

// 🔥 LOGIN CON RECONOCIMIENTO FACIAL IA (Público, recibe el email y el descriptor de la cámara) 🔥
Route::post('/login-faceid', [AuthController::class, 'loginFaceId']);

// Expone el catálogo de productos para que cualquier visitante de la tienda pueda visualizar el inventario disponible.
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// 🔥 DEJAMOS PÚBLICAS SOLO LAS RUTAS DE RETORNO DE PAYPAL 🔥
// (Porque cuando PayPal nos redirige, no manda el token de Vue)
Route::get('/paypal/success', [PaypalController::class, 'capturePayment']);
Route::get('/paypal/cancel', [PaypalController::class, 'cancelPayment']);

// Agrupa las rutas protegidas del sistema, asegurando que solo los usuarios autenticados mediante Sanctum puedan acceder a ellas.
Route::middleware('auth:sanctum')->group(function () {

    // LA GRAN CORRECCIÓN: CREATE PAYMENT AHORA ESTÁ PROTEGIDO
    // Ahora Laravel sí leerá el token y sabrá perfectamente quién está comprando.
    Route::post('/paypal/create', [PaypalController::class, 'createPayment']);

    // Retorna la información personal completa del usuario que actualmente mantiene una sesión activa.
    Route::get('/user', function (Request $request) {
        return $request->user()->load('persona');
    });

    // 🔥 LA NUEVA RUTA ESTRATÉGICA: GUARDAR LOS 128 PUNTOS DEL ROSTRO CON IA 🔥
    // Aquí es donde tu PerfilView.vue enviará el array de números extraídos por la cámara web.
    Route::post('/user/save-face', [AuthController::class, 'saveFace']);

    // Facilita al usuario autenticado modificar ciertos aspectos de su perfil o sus preferencias de ingreso biométrico.
    Route::post('/user/toggle-biometrics', [AuthController::class, 'toggleBiometrics']);
    Route::post('/user/update', [AuthController::class, 'updateProfile']);

    // Controla la papelera de reciclaje de los productos del inventario, haciendo posible ver y recuperar elementos eliminados previamente.
    Route::get('/trash/products', [ProductController::class, 'trash']);
    Route::post('/products/{id}/restore', [ProductController::class, 'restore']);

    // Declara las rutas estándar para realizar la gestión completa de inventarios y usuarios con rango de FBO en la plataforma.
    Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    Route::apiResource('fbos', FboController::class);

    // Administra el módulo de transacciones comerciales, listando el historial de compras y procesando las nuevas ventas y sus impuestos.
    Route::get('/my-sales', [VentaController::class, 'mySales']);
    Route::apiResource('sales', VentaController::class);
    Route::post('/sales/calculate-taxes', [VentaController::class, 'calculateTaxes']);

    // Genera documentos e informes descargables asociados a las ventas para facilitar el control contable del negocio.
    Route::get('/sales/{id}/pdf', [VentaController::class, 'generarPdf']);
    Route::get('/sales/export/excel', [VentaController::class, 'exportExcel']);

    // Habilita las operaciones vinculadas al registro y listado de los clientes de la tienda.
    Route::get('/clientes', [ClienteController::class, 'index']);
    Route::post('/clientes', [ClienteController::class, 'store']);

    // Concentra las funciones exclusivas para los administradores, brindándoles herramientas para aprobar solicitudes de cuentas, promover usuarios y moderar el acceso.
    Route::prefix('admin')->group(function () {
        Route::get('/requests', [AdminController::class, 'pendingRequests']);
        Route::post('/users/{id}/respond', [AdminController::class, 'respond']);
        Route::get('/users', [AdminController::class, 'allUsers']);
        Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
        Route::post('/users/{id}/promote', [AdminController::class, 'promote']);
    });

    // Cierra la sesión de forma segura y revoca los tokens de acceso del usuario actual.
    Route::post('/logout', [AuthController::class, 'logout']);
});