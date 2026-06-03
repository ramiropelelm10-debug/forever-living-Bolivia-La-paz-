<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'persona_id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'tipo_usuario',
        'mensaje_solicitud',
        'otp_code',
        'otp_expires_at',
        'is_trusted_device', 
        'biometrics_enabled',
        'foto_persona',
        'discount_percent',
        'nit_ci',
        'razon_social',
        'trusted_device_token', // 🔥 VITAL PARA EL RECORDAR
        'trusted_until'        // 🔥 VITAL PARA EL RECORDAR
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'otp_expires_at' => 'datetime', // 🔥 PARA QUE FUNCIONE EL TIEMPO EN BOLIVIA
            'is_trusted_device' => 'boolean',
            'biometrics_enabled' => 'boolean',
            'discount_percent' => 'decimal:2',
            'trusted_until' => 'datetime',  // 🔥 PARA QUE EL TOKEN NO EXPIRE ANTES
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    public function fbo(): HasOne
    {
        return $this->hasOne(Fbo::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }
}