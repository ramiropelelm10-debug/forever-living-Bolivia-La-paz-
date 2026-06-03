<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('otp_expires_at')->nullable();
            // 🔥 AÑADIMOS ESTAS DOS PARA LA CONFIANZA:
            $table->string('trusted_device_token')->nullable();
            $table->timestamp('trusted_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp_expires_at', 'trusted_device_token', 'trusted_until']);
        });
    }
};