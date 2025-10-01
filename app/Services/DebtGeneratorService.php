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

        // ✅ CORREGIDO: Solo propiedades ACTIVAS (no cortadas/inactivas)
        $properties = Property::where('estado', 'activo') // ← FILTRO CRÍTICO
            ->activas()
            ->with(['cliente', 'tariff'])
            ->get();

        foreach ($properties as $property) {
            DB::beginTransaction();
            
            try {
                $result['properties_processed']++;
                
                // Verificar si ya existe deuda para este mes y propiedad
                $existingDebt = Debt::where('propiedad_id', $property->id)
                    ->whereYear('fecha_emision', $month->year)
                    ->whereMonth('fecha_emision', $month->month)
                    ->first();

                if ($existingDebt && !$force) {
                    $result['debts_skipped']++;
                    DB::commit();
                    continue;
                }

                if ($existingDebt && $force) {
                    $existingDebt->delete();
                }

                // Calcular fechas
                $fechaEmision = $month->copy()->startOfMonth();
                $fechaVencimiento = $fechaEmision->copy()->addDays(15);

                // Obtener tarifa
                $tarifa = $property->tariff ?? Tariff::first();
                
                if (!$tarifa) {
                    throw new \Exception("No hay tarifas configuradas para la propiedad: {$property->referencia}");
                }

                // Crear la deuda
                Debt::create([
                    'propiedad_id' => $property->id,
                    'tarifa_id' => $tarifa->id,
                    'monto_pendiente' => $tarifa->precio_mensual,
                    'fecha_emision' => $fechaEmision,
                    'fecha_vencimiento' => $fechaVencimiento,
                    'estado' => 'pendiente',
                    'pagada_adelantada' => false,
                ]);

                $result['debts_created']++;
                DB::commit();
                
            } catch (\Exception $e) {
                DB::rollBack();
                $errorMsg = "Propiedad {$property->referencia}: " . $e->getMessage();
                $result['errors'][] = $errorMsg;
                Log::error($errorMsg);
            }
        }

        return $result;
    }

    /**
     * Genera deudas para un rango de meses (útil para migraciones)
     */
    public function generateDebtsForDateRange(Carbon $startDate, Carbon $endDate): array
    {
        $result = [
            'months_processed' => 0,
            'total_debts_created' => 0
        ];

        $currentMonth = $startDate->copy()->startOfMonth();
        
        while ($currentMonth->lte($endDate)) {
            $monthResult = $this->generateDebtsForMonth($currentMonth);
            
            $result['months_processed']++;
            $result['total_debts_created'] += $monthResult['debts_created'];
            $result["month_{$currentMonth->format('Y_m')}"] = $monthResult;
            
            $currentMonth->addMonth();
        }

        return $result;
    }
}