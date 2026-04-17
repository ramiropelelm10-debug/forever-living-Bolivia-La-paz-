<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('ventas');
            }
            if (!Schema::hasColumn('users', 'otp_code')) {
                $table->string('otp_code')->nullable();
            }
            if (!Schema::hasColumn('users', 'is_trusted_device')) {
                $table->boolean('is_trusted_device')->default(false);
            }
        });
    }

    public function down(): void
    {
        // No es necesario reversar en este caso
    }
};
