<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDeVenta extends Model
{
    use HasFactory;

    protected $table = 'item_de_ventas';

    protected $fillable = [
        'venta_id',
        'product_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    /**
     * RELACIÓN: Este ítem pertenece a una venta (cabecera).
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * RELACIÓN: Este ítem es un producto específico.
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}