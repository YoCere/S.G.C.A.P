<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            // ✅ RELACIONES ESENCIALES
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('propiedad_id')->constrained('propiedades');
            
            // ✅ INFORMACIÓN DEL PAGO
            $table->decimal('monto', 10, 2);
            $table->string('mes_pagado'); // Ej: "2024-10", "2024-11"
            $table->date('fecha_pago');
            
            // ✅ MÉTODO DE PAGO SIMPLE
            $table->enum('metodo', ['efectivo', 'transferencia', 'qr'])->default('efectivo');
            $table->string('comprobante')->nullable();
            
            // ✅ INFORMACIÓN ADICIONAL
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            
            $table->timestamps();
            
            // ✅ ÍNDICES PARA BÚSQUEDA RÁPIDA
            $table->index(['cliente_id', 'fecha_pago']);
            $table->index(['propiedad_id', 'mes_pagado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};