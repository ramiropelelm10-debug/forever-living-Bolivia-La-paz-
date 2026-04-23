<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente (Mass Assignment).
     * Estos deben coincidir exactamente con las columnas de tu tabla en PostgreSQL.
     */
    protected $fillable = [
        'nro_factura', 
        'user_id', 
        'product_id', 
        'cantidad', 
        'cliente_nit', 
        'cliente_nombre', 
        'monto_total', 
        'monto_iva', 
        'monto_it', 
        'monto_neto', 
        'total_cc'
    ];

    /**
     * RELACIÓN: Una venta pertenece a un producto.
     * Esto te permite hacer $sale->product->name en el panel.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * RELACIÓN: Una venta pertenece a un usuario (FBO).
     * Esto te permite hacer $sale->user->name para saber quién vendió.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * CASTING DE DATOS:
     * Asegura que los números siempre se traten como decimales/flotantes en PHP.
     */
    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_iva'   => 'decimal:2',
        'monto_it'    => 'decimal:2',
        'monto_neto'  => 'decimal:2',
        'total_cc'    => 'decimal:3',
        'cantidad'    => 'integer',
    ];
}