<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (Schema::hasColumn('deudas','cliente_id')) {
            Schema::table('deudas', function (Blueprint $t) {
                // si tenía FK, esto la elimina junto a la columna
                $t->dropConstrainedForeignId('cliente_id');
            });
        }
    }
    public function down(): void {
        Schema::table('deudas', function (Blueprint $t) {
            if (!Schema::hasColumn('deudas','cliente_id')) {
                $t->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            }
        });
    }
};
