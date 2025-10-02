<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('numero_recibo')->nullable()->after('id');
        });

        // Generar números de recibo para registros existentes (si los hay)
        if (DB::table('pagos')->exists()) {
            $pagos = DB::table('pagos')->get();
            foreach ($pagos as $index => $pago) {
                DB::table('pagos')
                    ->where('id', $pago->id)
                    ->update([
                        'numero_recibo' => 'REC-' . str_pad($pago->id, 6, '0', STR_PAD_LEFT)
                    ]);
            }
        }

        // Hacer la columna NOT NULL después de poblarla
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('numero_recibo')->nullable(false)->change();
            $table->unique('numero_recibo');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropUnique(['numero_recibo']);
            $table->dropColumn('numero_recibo');
        });
    }
};