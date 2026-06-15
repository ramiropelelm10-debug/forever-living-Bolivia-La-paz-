<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan; // <-- IMPORTANTE: Traemos el motor de comandos

Route::get('/', function () {
    return view('welcome');
});

// 🔥 EL TRUCO SECRETO PARA ACTIVAR LA BASE DE DATOS EN RENDER FREE 🔥
Route::get('/instalar-bd', function () {
    Artisan::call('migrate', ['--force' => true]);
    return '¡Base de datos creada con éxito, ingeniero!';
});