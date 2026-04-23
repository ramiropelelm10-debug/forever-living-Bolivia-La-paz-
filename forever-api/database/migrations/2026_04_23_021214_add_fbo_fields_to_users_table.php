<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // Aquí guardaremos el descuento que tú les asignes (ej: 15% o 30%) 
        $table->decimal('discount_percent', 5, 2)->default(0.00)->after('email'); 
        
        // Datos para la Factura Profesional que pediste
        $table->string('nit_ci')->nullable()->after('discount_percent'); 
        $table->string('razon_social')->nullable()->after('nit_ci'); 
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['discount_percent', 'nit_ci', 'razon_social']);
    });
}
};
