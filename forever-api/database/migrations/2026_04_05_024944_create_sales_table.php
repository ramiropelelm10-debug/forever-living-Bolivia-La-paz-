<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('nro_factura')->unique();
            $table->foreignId('user_id')->constrained(); // Quién vende
            $table->foreignId('product_id')->constrained(); // <-- AGREGADO: Qué vende
            $table->integer('cantidad'); // <-- AGREGADO: Cuánto vende
            
            $table->string('cliente_nit')->nullable();
            $table->string('cliente_nombre')->nullable();
            
            // Cálculos para Forever Bolivia
            $table->decimal('monto_total', 12, 2); // Total Bs
            $table->decimal('monto_iva', 12, 2);   // 13%
            $table->decimal('monto_it', 12, 2);    // 3%
            $table->decimal('monto_neto', 12, 2);  // Total - Impuestos
            $table->decimal('total_cc', 10, 3);    // Puntos Forever
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};