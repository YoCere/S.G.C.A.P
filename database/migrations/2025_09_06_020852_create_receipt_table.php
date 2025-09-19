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
        Schema::create('recibos', function (Blueprint $table) {
            $table->id();
            $table->boolean('emitido')->default(true);

            // Relaciones
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')->nullable()
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Campos propios del recibo
            $table->date('periodo_facturado'); 
            $table->decimal('monto_total', 12, 2);
            $table->decimal('monto_multa', 12, 2)->default(0);
            $table->string('referencia', 255)->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibos');
    }
};
