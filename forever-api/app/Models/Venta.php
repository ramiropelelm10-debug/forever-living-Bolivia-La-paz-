<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    // Usamos 'ventas' porque ese es el nombre que pusimos en la migración
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

    public function items(): HasMany
    {
        return $this->hasMany(ItemDeVenta::class);
    }
}