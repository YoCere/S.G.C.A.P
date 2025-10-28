<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('multas', function (Blueprint $table) {
            $table->id();
            
            // ✅ CORREGIDO: Relaciones REQUERIDAS
            $table->foreignId('deuda_id')->nullable()->constrained('deudas')->onDelete('cascade');
            $table->foreignId('propiedad_id')->constrained('propiedades')->onDelete('cascade');
            
            $table->enum('tipo', [
                'reconexion_3meses',
                'reconexion_12meses', 
                'conexion_clandestina',
                'manipulacion_llaves',
                'construccion',
                'otro'
            ])->default('otro');
            
            $table->string('nombre');
            $table->decimal('monto', 10, 2)->default(0);
            $table->text('descripcion')->nullable();
            $table->date('fecha_aplicacion');
            
            // ✅ CORREGIDO: Solo estados de multa
            $table->enum('estado', ['pendiente', 'pagada', 'anulada'])->default('pendiente');
            
            $table->boolean('aplicada_automaticamente')->default(false);
            $table->boolean('activa')->default(true);
            $table->foreignId('creado_por')->constrained('users')->onDelete('cascade');
            
            $table->timestamps();
            $table->softDeletes();
            
            // ✅ AGREGADO: Índices
            $table->index(['estado', 'activa']);
            $table->index(['tipo', 'fecha_aplicacion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('multas');
    }
};