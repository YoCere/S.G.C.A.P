<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Debt;
use App\Models\Tariff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DebtGeneratorService
{
    public function generateDebtsForMonth(Carbon $month, bool $force = false): array
    {
        $result = [
            'properties_processed' => 0,
            'debts_created' => 0,
            'debts_skipped' => 0,
            'errors' => []
        ];

        // ✅ CORREGIDO: Solo propiedades ACTIVAS con relación correcta
        $properties = Property::where('estado', 'activo')
            ->with(['client', 'tariff']) // ← CORREGIDO: 'client' no 'cliente'
            ->get();

        Log::info("Generando deudas para {$month->format('F Y')}. Propiedades activas: " . $properties->count());

        foreach ($properties as $property) {
            DB::beginTransaction();
            
            try {
                $result['properties_processed']++;
                
                // ✅ CORREGIDO: Verificación más precisa de deuda existente
                $mesFormato = $month->format('Y-m');
                $existingDebt = Debt::where('propiedad_id', $property->id)
                    ->whereYear('fecha_emision', $month->year)
                    ->whereMonth('fecha_emision', $month->month)
                    ->first();

                if ($existingDebt) {
                    if (!$force) {
                        $result['debts_skipped']++;
                        Log::info("Deuda ya existe para {$property->referencia} - {$mesFormato}. Omitiendo.");
                        DB::commit();
                        continue;
                    } else {
                        // Si force=true, eliminar la deuda existente
                        $existingDebt->delete();
                        Log::info("Deuda existente eliminada para {$property->referencia} - {$mesFormato} (force=true)");
                    }
                }

                // ✅ CORREGIDO CRÍTICO: Fechas calculadas CORRECTAMENTE
                $fechaEmision = $month->copy()->startOfMonth();
                $fechaVencimiento = $month->copy()->endOfMonth(); // ← ¡CORREGIDO: fin de mes, no 15 días!

                Log::info("Fechas para {$property->referencia}: Emisión: {$fechaEmision->format('Y-m-d')}, Vencimiento: {$fechaVencimiento->format('Y-m-d')}");

                // Obtener tarifa
                $tarifa = $property->tariff ?? Tariff::where('activo', true)->first();
                
                if (!$tarifa) {
                    throw new \Exception("No hay tarifas activas configuradas para la propiedad: {$property->referencia}");
                }

                // ✅ CORREGIDO: Crear la deuda con fechas correctas
                Debt::create([
                    'propiedad_id' => $property->id,
                    // ❌ ELIMINADO: tarifa_id (redundante)
                    'monto_pendiente' => $tarifa->precio_mensual,
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 'pendiente',
                    // ❌ ELIMINADO: pagada_adelantada (no se usa)
                ]);
    

                $result['debts_created']++;
                Log::info("✅ Deuda creada para {$property->referencia} - {$mesFormato}");
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $errorMsg = "Propiedad {$property->referencia}: " . $e->getMessage();
                $result['errors'][] = $errorMsg;
                Log::error($errorMsg);
            }
        }

        Log::info("Generación completada: {$result['debts_created']} deudas creadas, {$result['debts_skipped']} omitidas");
        return $result;
    }

    /**
     * Genera deudas para un rango de meses (útil para migraciones)
     */
    public function generateDebtsForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $result = [
            'months_processed' => 0,
            'total_debts_created' => 0,
            'monthly_results' => []
        ];

        $currentMonth = $startDate->copy()->startOfMonth();
        
        Log::info("Iniciando generación de deudas desde {$startDate->format('Y-m')} hasta {$endDate->format('Y-m')}");

        while ($currentMonth->lte($endDate)) {
            $monthResult = $this->generateDebtsForMonth($currentMonth);
            
            $result['months_processed']++;
            $result['total_debts_created'] += $monthResult['debts_created'];
            $result['monthly_results'][$currentMonth->format('Y-m')] = $monthResult;
            
            Log::info("Mes {$currentMonth->format('Y-m')}: {$monthResult['debts_created']} deudas creadas");
            
            $currentMonth->addMonth();
        }

        Log::info("Generación por rango completada: {$result['total_debts_created']} deudas totales");
        return $result;
    }
}