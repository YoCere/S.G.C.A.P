<?php

namespace App\Console\Commands;

use App\Services\DebtGeneratorService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class GenerateMonthlyDebts extends Command
{
    protected $signature = 'debts:generate-monthly 
                            {--month= : Mes en formato YYYY-MM (por defecto mes actual)}
                            {--force : Forzar generación incluso si ya existen deudas para el mes}';
    
    protected $description = 'Genera deudas mensuales para todas las propiedades activas';

    public function __construct(protected DebtGeneratorService $debtService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $month = $this->option('month') ? Carbon::parse($this->option('month')) : now();
        $force = $this->option('force');

        $this->info("Iniciando generación de deudas para: " . $month->format('F Y'));
        
        try {
            $result = $this->debtService->generateDebtsForMonth($month, $force);
            
            $this->info("✅ Deudas generadas exitosamente:");
            $this->info("   - Propiedades procesadas: " . $result['properties_processed']);
            $this->info("   - Deudas creadas: " . $result['debts_created']);
            $this->info("   - Deudas omitidas (ya existían): " . $result['debts_skipped']);
            
            if (!empty($result['errors'])) {
                $this->warn("   - Errores: " . count($result['errors']));
                foreach ($result['errors'] as $error) {
                    $this->error("     * " . $error);
                }
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Error generando deudas: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}