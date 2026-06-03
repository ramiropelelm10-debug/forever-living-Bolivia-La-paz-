<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Venta extends Model
{
    protected $table = 'ventas'; 

    protected $fillable = [
        'nro_factura', 
        'user_id', 
        'nit_ci', 
        'razon_social', 
        'monto_total', 
        'monto_iva', 
        'total_cc'
    ];

    /**
     * Relación con el usuario (FBO/Admin) que registró la venta
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los detalles de los productos vendidos
     */
    public function items(): HasMany
    {
        return $this->hasMany(ItemDeVenta::class);
    }
}