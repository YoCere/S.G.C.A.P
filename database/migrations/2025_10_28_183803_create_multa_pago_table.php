<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multa_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('multa_id')->constrained('multas')->onDelete('cascade');
            $table->foreignId('pago_id')->constrained('pagos')->onDelete('cascade');
            $table->decimal('monto_pagado', 10, 2);
            $table->timestamps();
            
            $table->unique(['multa_id', 'pago_id']); // Evitar duplicados
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multa_pago');
    }
};