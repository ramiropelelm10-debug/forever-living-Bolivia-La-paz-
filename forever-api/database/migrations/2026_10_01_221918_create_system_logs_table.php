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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            // Relación con el usuario (puede ser nulo si es una acción del sistema)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Campos para registrar la actividad
            $table->string('action'); // Ej: 'CREAR', 'EDITAR', 'ELIMINAR', 'LOGIN'
            $table->string('module'); // Ej: 'FBO', 'Ventas', 'Usuarios', 'Auth'
            $table->text('description'); // Detalles exactos de lo que se hizo
            $table->ipAddress('ip_address')->nullable(); // Para rastrear desde dónde se hizo
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_logs');
    }
};