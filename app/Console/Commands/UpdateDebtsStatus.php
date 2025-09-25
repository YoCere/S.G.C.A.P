<?php

namespace App\Console\Commands;

use App\Models\Debt;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateDebtsStatus extends Command
{
    protected $signature = 'debts:update-status';
    
    protected $description = 'Actualiza automáticamente el estado de deudas pendientes a vencidas';

    public function handle()
    {
        $today = now()->format('Y-m-d');
        $this->info("Actualizando estados de deudas para: {$today}");

        $debtsToUpdate = Debt::where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', $today)
            ->get();

        $updatedCount = 0;

        foreach ($debtsToUpdate as $debt) {
            $debt->update(['estado' => 'vencida']);
            $updatedCount++;
            
            $this->line("Deuda #{$debt->id} vencida el {$debt->fecha_vencimiento->format('Y-m-d')} → actualizada a VENCIDA");
        }

        $this->info("✅ Actualización completada:");
        $this->info("   - Deudas actualizadas: {$updatedCount}");
        
        if ($updatedCount === 0) {
            $this->info("   - No hay deudas pendientes que hayan vencido hoy");
        }
        
        return 0;
    }
}