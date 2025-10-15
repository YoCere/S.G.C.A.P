<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deudas', function (Blueprint $table) {
            $table->id();
            
            // ✅ CORREGIDO: Solo relación esencial con propiedad
            $table->foreignId('propiedad_id')->constrained('propiedades')->cascadeOnDelete();
            
            // ❌ ELIMINADO: tarifa_id redundante
            // ❌ ELIMINADO: cliente_id redundante
            
            $table->decimal('monto_pendiente', 10, 2);
            $table->date('fecha_emision')->useCurrent();
            $table->date('fecha_vencimiento')->nullable();
            
            // ✅ CORREGIDO: Solo estados de deuda, no duplicados de propiedad
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'anulada'])->default('pendiente');
            
            // ❌ ELIMINADO: pagada_adelantada (no se usa)
            
            // 1 deuda por propiedad y mes:
            $table->unique(['propiedad_id','fecha_emision']); 
            
            $table->timestamps();
            
            // ✅ AGREGADO: Índices para consultas frecuentes
            $table->index(['estado', 'fecha_vencimiento']);
            $table->index(['fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deudas');
    }
};