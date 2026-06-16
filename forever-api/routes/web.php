<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

Route::get('/', function () {
    return view('welcome');
});

// 🔥 LA RUTA SECRETA PARA INSTALAR LA BASE DE DATOS 🔥
Route::get('/instalar-bd', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return '¡Base de datos creada con éxito, ingeniero!';
    } catch (\Exception $e) {
        return '🚨 ERROR REAL DE LARAVEL: ' . $e->getMessage();
    }
});

// 🔥 LA NUEVA RUTA SECRETA PARA ASCENDER AL PRIMER ADMIN 🔥
Route::get('/hacerme-admin', function () {
    try {
        // Buscamos al primer usuario que acabas de registrar en la tienda
        $user = User::first();
        
        if ($user) {
            // Le damos poderes máximos (ajusta el nombre de la columna si en tu BD se llama 'tipo_usuario')
            $user->role = 'admin'; 
            
            // Si tienes un campo para aprobar cuentas, lo forzamos a true o 1
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'is_active')) {
                $user->is_active = true; 
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'status')) {
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