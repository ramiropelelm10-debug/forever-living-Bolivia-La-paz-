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
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('is_trusted_device')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ELIMINAMOS 'otp_code' de esta lista porque este archivo no lo creó
            $table->dropColumn(['otp_expires_at', 'is_trusted_device']);
        });
    }
};
