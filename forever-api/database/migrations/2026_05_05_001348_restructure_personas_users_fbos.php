<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. CREAMOS LA TABLA PERSONAS (Datos físicos y reales)
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombres');
            $table->string('apellidos');
            $table->string('ci')->unique()->nullable();
            $table->string('telefono')->nullable();
            $table->timestamps();
        });

        // 2. VINCULAMOS PERSONA CON USERS
        Schema::table('users', function (Blueprint $table) {
            // Agregamos la llave foránea persona_id justo después del id del usuario
            $table->foreignId('persona_id')->nullable()->after('id')->constrained('personas')->onDelete('cascade');
        });

        // 3. REESTRUCTURAMOS LA TABLA FBOs
        // Como estamos normalizando, borramos la versión vieja y creamos la limpia
        Schema::dropIfExists('fbos');
        
        Schema::create('fbos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Vinculado al usuario
            $table->string('fbo_id')->unique(); // El código de Forever Living (Ej: 591000...)
            $table->decimal('discount_rate', 5, 2)->default(0.00); // 15%, 30%, etc.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fbos');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
        });
        
        Schema::dropIfExists('personas');
    }
};