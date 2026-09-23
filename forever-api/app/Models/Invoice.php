<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    // Se define el nombre exacto de la tabla en PostgreSQL
    protected $table = 'invoices';

    // Atributos permitidos para guardar datos masivamente (Seguridad)
    protected $fillable = [
        'invoice_number', // Número de factura generado
        'client_name',    // Nombre del cliente
        'nit',            // NIT o carnet de identidad
        'total_amount',   // Monto total cobrado
        'status',         // Estado actual (Ej: Emitida, Anulada)
        'venta_id',       // Llave foránea de la transacción
        'pdf_url'         // Enlace al recibo generado
    ];

    // Relación escalable: Una factura siempre pertenece a una venta
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}