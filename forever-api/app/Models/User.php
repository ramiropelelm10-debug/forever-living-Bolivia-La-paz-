<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Laravel\Sanctum\HasApiTokens; 

#[Fillable([
    'name', 
    'email', 
    'password', 
    'role',
    'otp_code', 
    'otp_expires_at', 
    'is_trusted_device',
    'biometrics_enabled' // <-- IMPORTANTE: Agrégalo aquí para que Laravel te deje guardarlo
])]
#[Hidden(['password', 'remember_token', 'otp_code'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Unificamos todos los "casts" aquí (Versión Laravel 11)
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime', 
            'is_trusted_device' => 'boolean',
            'biometrics_enabled' => 'boolean', // <-- Movido aquí para mayor orden
        ];
    }
}