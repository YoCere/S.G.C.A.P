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
            $table->string('numero_recibo'); // ✅ MANTENIDO: Sin unique pero requerido
            
            // ✅ CORREGIDO: Solo relación esencial
            $table->foreignId('propiedad_id')->constrained('propiedades');
            
            // ❌ ELIMINADO: cliente_id redundante
            
            $table->decimal('monto', 10, 2);
            $table->string('mes_pagado');
            $table->date('fecha_pago');
            
            $table->enum('metodo', ['efectivo', 'transferencia', 'qr'])->default('efectivo');
            $table->string('comprobante')->nullable();
            
            $table->text('observaciones')->nullable();
            $table->foreignId('registrado_por')->constrained('users');
            
            $table->timestamps();
            
            // ✅ MEJORADO: Índices optimizados
            $table->index(['propiedad_id', 'mes_pagado']);
            $table->index(['fecha_pago']);
            $table->index(['numero_recibo']); // Índice sin unique
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};