<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deuda_recibo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recibo_id')->constrained('recibos')->cascadeOnDelete();
            $table->foreignId('deuda_id')->constrained('deudas')->cascadeOnDelete();
            $table->decimal('monto_aplicado', 12, 2);
            $table->timestamps();

            $table->unique(['recibo_id','deuda_id']);
            $table->index(['deuda_id','recibo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deuda_recibo');
    }
};
