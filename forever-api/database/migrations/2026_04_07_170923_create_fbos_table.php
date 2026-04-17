<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fbos', function (Blueprint $table) {
            $table->id();
            $table->string('fbo_id')->unique(); 
            $table->string('name');
            $table->string('last_name')->nullable();
            $table->string('dni')->nullable();
            $table->string('email')->nullable();
            $table->string('level')->default('Novus');
            $table->decimal('discount_rate', 5, 2)->default(0.00);
            $table->string('sponsor_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fbos');
    }
};