<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    // ESTO ES LO QUE FALTA: Autorizar los campos de tu pgAdmin
   protected $fillable = [
    'nro_factura', 'user_id', 'product_id', 'cantidad', 
    'cliente_nit', 'cliente_nombre', 'monto_total', 
    'monto_iva', 'monto_it', 'monto_neto', 'total_cc'
];

    // Relaciones (Opcional pero recomendado)
    public function product() { return $this->belongsTo(Product::class); }
    public function user() { return $this->belongsTo(User::class); }
}