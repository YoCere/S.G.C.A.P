<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('config_multa_moras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->default('Multa por mora');
            $table->text('descripcion')->nullable();
            $table->integer('meses_gracia')->default(3)->comment('Meses antes de aplicar multa');
            $table->decimal('porcentaje_multa', 5, 2)->default(10.00)->comment('Porcentaje de multa');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // Insertar configuración por defecto
        DB::table('config_multa_moras')->insert([
            'nombre' => 'Multa por mora estándar',
            'descripcion' => 'Se aplica después de 3 meses de atraso, 10% sobre el monto base',
            'meses_gracia' => 3,
            'porcentaje_multa' => 10.00,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('config_multa_moras');
    }
};