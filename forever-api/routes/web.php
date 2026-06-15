<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 LA RUTA SECRETA CON CAZADOR DE ERRORES 🔥
Route::get('/instalar-bd', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '¡Base de datos creada con éxito, ingeniero!';
    } catch (\Exception $e) {
        return '🚨 ERROR REAL DE LARAVEL: ' . $e->getMessage();
    }
});