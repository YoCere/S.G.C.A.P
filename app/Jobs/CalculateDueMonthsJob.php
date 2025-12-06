<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Property;

class CalculateDueMonthsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 1200; // 20 min por ejemplo

    public function __construct()
    {
    }

    public function handle()
    {
        $lockKey = 'calculate_due_months_running_lock_v1';
        // Intentar crear lock simple en cache para evitar ejecuciones simultáneas
        if (! Cache::add($lockKey, true, 60)) {
            Log::info("CalculateDueMonthsJob: otra instancia está corriendo -> abortando.");
            return;
        }

        $logStart = "[".now()."] CalculateDueMonthsJob: inicio\n";
        Log::info($logStart);

        try {
            $properties = Property::all(); // si es mucha data, paginate y despachar jobs por chunk
            foreach ($properties as $p) {
                // llamamos al método (que usa cache internamente)
                $p->obtenerMesesAdeudados();
            }
            Log::info("CalculateDueMonthsJob: completado correctamente. Procesadas: " . $properties->count());
        } catch (\Throwable $e) {
            Log::error("CalculateDueMonthsJob ERROR: " . $e->getMessage(), ['exception' => $e]);
        } finally {
            Cache::forget($lockKey);
        }
    }
}
