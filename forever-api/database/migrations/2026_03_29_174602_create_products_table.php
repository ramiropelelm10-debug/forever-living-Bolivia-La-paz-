<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); 
            $table->string('name');
            // Usamos TEXT porque las imágenes en Base64 son cadenas de texto muy largas
            $table->text('foto_persona')->nullable(); 
            $table->decimal('price_bs', 10, 2); 
            $table->decimal('cc_value', 10, 3); 
            $table->integer('stock')->default(0);
            $table->softDeletes(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};