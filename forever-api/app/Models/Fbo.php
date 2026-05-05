<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fbo extends Model
{
    // Solo estos campos son permitidos ahora para evitar errores con la nueva base de datos
    protected $fillable = [
        'user_id', 
        'fbo_id', 
        'discount_rate'
    ];

    /**
     * RELACIÓN: Un FBO pertenece a un Usuario (User).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}