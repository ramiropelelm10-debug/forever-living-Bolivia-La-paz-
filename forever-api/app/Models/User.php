<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'persona_id', // <-- NUEVO: Vínculo con la tabla personas
    'name', 
    'email', 
    'password', 
    'role',
    'otp_code', 
    'otp_expires_at', 
    'is_trusted_device',
    'biometrics_enabled',
    'discount_percent', // <-- NUEVO: Descuento FBO
    'nit_ci',           // <-- NUEVO: Datos fiscales
    'razon_social'      // <-- NUEVO: Datos fiscales
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
            'biometrics_enabled' => 'boolean',
            'discount_percent' => 'decimal:2', // Cast para decimales
        ];
    }

    // ==========================================
    // RELACIONES (NUEVAS)
    // ==========================================

    /**
     * Relación con los datos físicos de la persona
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /**
     * Relación con el perfil de Distribuidor FBO
     */
    public function fbo(): HasOne
    {
        return $this->hasOne(Fbo::class);
    }

    /**
     * Relación con las ventas procesadas
     */
    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}