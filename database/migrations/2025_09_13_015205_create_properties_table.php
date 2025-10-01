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
        Schema::create('propiedades', function (Blueprint $table) {
            $table->id();
           // FKs (tablas en inglés, columnas en español)
           $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
           $table->foreignId('tarifa_id')->constrained('tarifas');

           $table->string('referencia');
           // ✅ NUEVO: Campo barrio agregado
           $table->enum('barrio', [
               'Centro', 
               'Aroma', 
               'Los Valles', 
               'Caipitandy', 
               'Primavera',
               'Arboleda'
            ])->nullable();
           
           $table->decimal('latitud', 10, 8)->nullable();
           $table->decimal('longitud', 11, 8)->nullable();

           $table->enum('estado', ['activo', 'inactivo', 'cortado'])->default('activo')->index();
            $table->timestamps();        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('propiedades'); // ← CORREGIDO: 'propiedades' no 'properties'
    }
};