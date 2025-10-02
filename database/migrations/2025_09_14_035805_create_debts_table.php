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
        Schema::create('deudas', function (Blueprint $table) {
            $table->id();
            // Relación con cliente
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();

            // Campos propios de la deuda
            $table->foreignId('propiedad_id')->constrained('propiedades')->cascadeOnDelete();
            $table->foreignId('tarifa_id')->constrained('tarifas');   // <-- AÑADIDO
            $table->decimal('monto_pendiente', 10, 2);
            $table->date('fecha_emision')->useCurrent();
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('estado', ['pendiente', 'pagada', 'vencida', 'anulada'])
            ->default('pendiente');
            $table->boolean('pagada_adelantada')->default(false);   
            // 1 deuda por propiedad y mes:
            $table->unique(['propiedad_id','fecha_emision']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deudas');
    }
};
