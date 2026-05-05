<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminamos la tabla vieja si existe para evitar conflictos
        Schema::dropIfExists('sales');

        // 2. Creamos la CABECERA de la factura (Datos generales)
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->string('nro_factura')->unique();
            $table->foreignId('user_id')->constrained('users'); // El FBO que vende
            $table->string('nit_ci')->nullable();
            $table->string('razon_social')->nullable();
            
            // Totales de TODA la compra
            $table->decimal('monto_total', 12, 2); 
            $table->decimal('monto_iva', 12, 2); 
            $table->decimal('total_cc', 10, 3); 
            
            $table->timestamps();
        });

        // 3. Creamos el DETALLE de la factura (Los productos de la bolsita)
        Schema::create('item_de_ventas', function (Blueprint $table) {
            $table->id();
            // Si se borra la venta, se borran sus items (onDelete cascade)
            $table->foreignId('venta_id')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 12, 2); // cantidad * precio_unitario
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_de_ventas');
        Schema::dropIfExists('ventas');
    }
};