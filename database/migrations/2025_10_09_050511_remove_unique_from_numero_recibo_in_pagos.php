<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Eliminar la restricción UNIQUE
            $table->dropUnique(['numero_recibo']);
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            // Restaurar la restricción UNIQUE
            $table->unique('numero_recibo');
        });
    }
};