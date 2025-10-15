<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio_mensual', 8, 2); // ✅ CORREGIDO: DECIMAL para dinero
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
            
            // ✅ AGREGADO: Índice para tarifas activas
            $table->index(['activo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifas'); // ✅ CORREGIDO: Coherente con 'tarifas'
    }
};