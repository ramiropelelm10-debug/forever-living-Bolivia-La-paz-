<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 LA RUTA SECRETA PARA INSTALAR LA BASE DE DATOS Y CORRER SEEDERS 🔥
Route::get('/instalar-bd', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
        return '¡Base de datos creada y seeders ejecutados con éxito, ingeniero!';
    } catch (\Exception $e) {
        return '🚨 ERROR REAL DE LARAVEL: ' . $e->getMessage();
    }
});

// 🔥 LA NUEVA RUTA SECRETA PARA ASCENDER AL PRIMER ADMIN 🔥
Route::get('/hacerme-admin', function () {
    try {
        $user = User::first();
        
        if ($user) {
            $user->role = 'admin'; 
            
            if (Schema::hasColumn('users', 'is_active')) {
                $user->is_active = true; 
            }
            if (Schema::hasColumn('users', 'status')) {
                $user->status = 'activo'; 
            }

            $user->save();
            
            return "¡Felicidades, {$user->name}! Ahora eres el Administrador Supremo de Forever Bolivia. Ya puedes entrar al panel y mostrarle el avance al inge.";
        }
        
        return "Primero tienes que registrar una cuenta desde la página de Vue.";
    } catch (\Exception $e) {
        return '🚨 ERROR AL ASCENDER ADMIN: ' . $e->getMessage();
    }
});

// 🔥 RUTA PARA ASCENDER UN USUARIO ESPECÍFICO A ADMIN 🔥
Route::get('/ascender-usuario/{email}', function ($email) {
    $user = User::where('email', $email)->first();
    if (!$user) return "Usuario {$email} no encontrado.";
    
    $user->role = 'admin'; 
    if (Schema::hasColumn('users', 'is_active')) $user->is_active = true;
    if (Schema::hasColumn('users', 'status')) $user->status = 'activo';
    $user->save();
    
    return "¡Felicidades, {$user->email}! Ahora eres Admin.";
});

// 🔥 LA RUTA PARA CAMBIAR CONTRASEÑA EN CASO DE ERROR 🔥
Route::get('/reset-password/{email}/{nuevaPassword}', function ($email, $nuevaPassword) {
    $user = User::where('email', $email)->first();
    if (!$user) {
        return "El usuario {$email} no existe en la base de datos.";
    }
    $user->password = Hash::make($nuevaPassword);
    $user->save();
    return "Contraseña actualizada con éxito para {$email}. Ahora intenta loguearte.";
});

// 🔥 RUTA DE DEBUG PARA VER QUIÉN VIVE EN LA BD 🔥
Route::get('/quien-vive-aqui', function () {
    $emails = User::pluck('email')->toArray();
    if (empty($emails)) {
        return "La base de datos está totalmente VACÍA. No hay usuarios registrados. Regístrate en la web primero.";
    }
    return "Usuarios registrados en la nube: " . implode(', ', $emails);
});

// 🔥 RUTA PARA VER EL OTP ACTIVO 🔥
Route::get('/ver-otp/{email}', function ($email) {
    try {
        $otpRecord = DB::table('otps')->where('email', $email)->latest()->first();
        if (!$otpRecord) {
            return "No hay OTP generado para {$email} en este momento. Intenta solicitarlo de nuevo en el login.";
        }
        return "El OTP actual para {$email} es: " . $otpRecord->code;
    } catch (\Exception $e) {
        return "Error al buscar el OTP: " . $e->getMessage();
    }
});