<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('tarifa_id')->constrained('tarifas'); // ✅ CORREGIDO: 'tarifas'
            
            $table->string('referencia');
            $table->enum('barrio', [
                'Centro', 'Aroma', 'Los Valles', 'Caipitandy', 'Primavera', 'Arboleda', 'Fatima'
            ])->nullable();
           
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();

            // 🆕 ACTUALIZADO: Agregar nuevo estado 'pendiente_conexion' y cambiar default
            $table->enum('estado', ['pendiente_conexion', 'activo', 'inactivo', 'cortado', 'corte_pendiente'])->default('pendiente_conexion')->index();
            $table->enum('tipo_trabajo_pendiente', [
                'conexion_nueva', 
                'corte_mora', 
                'reconexion'
            ])->nullable();
            $table->timestamps();
            
            // ✅ AGREGADO: Índice compuesto para búsquedas
            $table->index(['estado', 'barrio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('propiedades'); // ✅ CORREGIDO: Coherente
    }
};