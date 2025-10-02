<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Pago;
use App\Models\Debt;
use App\Models\Fine;
use App\Models\Tariff;
use App\Models\Property;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;  
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === 1) Usuarios ===
        $admin = User::firstOrCreate(
            ['email' => 'josealfredocerezorios75@gmail.com'],
            ['name' => 'Administrador', 'password' => bcrypt('7516naBJ')]
        );

        $empleados = User::factory(4)->create();

        // === 2) Tarifas base + aleatorias ===
        $tarifasBase = [
            ['nombre' => 'Normal',       'precio_mensual' => 40.00, 'descripcion' => 'Tarifa estándar para usuarios regulares'],
            ['nombre' => 'Adulto mayor', 'precio_mensual' => 25.00, 'descripcion' => 'Tarifa reducida para adultos mayores'],
        ];
        foreach ($tarifasBase as $t) {
            Tariff::firstOrCreate(['nombre' => $t['nombre']], Arr::except($t, 'nombre'));
        }
        Tariff::factory()->count(3)->create();

        // === 3) Clientes ===
        Client::factory(30)->create();

        // === 4) Propiedades (1–2 por cliente) con tarifa asignada ===
        Client::query()->chunkById(200, function ($clientes) {
            foreach ($clientes as $cli) {
                $num = rand(1, 2);
                for ($i = 0; $i < $num; $i++) {
                    Property::factory()->create([
                        'cliente_id' => $cli->id,
                        'tarifa_id'  => Tariff::inRandomOrder()->value('id'),
                        'estado'     => 'activo',
                    ]);
                }
            }
        });

        // === 5) Deudas para cada propiedad (últimos 3 meses incluyendo el actual) ===
        $meses = [
            now()->startOfMonth()->subMonths(2),
            now()->startOfMonth()->subMonth(),
            now()->startOfMonth(),
        ];

        Property::query()
        ->whereNotNull('tarifa_id')
        ->with('tariff:id,precio_mensual')
        ->chunkById(200, function ($props) use ($meses) {
            foreach ($props as $p) {
                $tarifaId = $p->tarifa_id ?: Tariff::inRandomOrder()->value('id');
                if (!$tarifaId) { continue; }

                $precio = optional($p->tariff)->precio_mensual
                        ?? optional(Tariff::find($tarifaId))->precio_mensual
                        ?? 0;

                foreach ($meses as $mes) {
                    Debt::firstOrCreate(
                        ['propiedad_id' => $p->id, 'fecha_emision' => $mes],
                        [
                            'tarifa_id'          => $tarifaId,
                            'monto_pendiente'    => $precio,
                            'fecha_vencimiento'  => $mes->copy()->addDays(30),
                            'estado'             => fake()->boolean(30) ? 'vencida' : 'pendiente',
                            'pagada_adelantada'  => false,
                        ]
                    );
                }
            }
        });
        
        $tarifaFallback = Tariff::inRandomOrder()->value('id') ?? Tariff::first()->id;
        Property::whereNull('tarifa_id')->update(['tarifa_id' => $tarifaFallback]);

        // === 6) Multas aleatorias sobre algunas deudas vencidas ===
        Debt::where('estado', 'vencida')
            ->inRandomOrder()
            ->take(30)
            ->get()
            ->each(function ($d) {
                Fine::factory(rand(1, 2))->create(['deuda_id' => $d->id]);
            });

        // === 7) ✅ CORREGIDO: PAGOS DE EJEMPLO (CON GENERACIÓN DE NUMERO_RECIBO) ===
        $cobradorId = $empleados->random()->id ?? $admin->id;

        // Función para generar número de recibo único
        $generarNumeroRecibo = function() {
            static $contador = 1;
            return 'REC-' . str_pad($contador++, 6, '0', STR_PAD_LEFT);
        };

        // Seleccionar algunas propiedades aleatorias para generar pagos
        Property::with(['client', 'tariff'])
            ->inRandomOrder()
            ->take(15)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, $generarNumeroRecibo) {
                
                // Generar 1-3 pagos por propiedad
                $cantidadPagos = rand(1, 3);
                
                for ($i = 0; $i < $cantidadPagos; $i++) {
                    // Mes aleatorio de los últimos 6 meses
                    $mesesAtras = rand(0, 5);
                    $mesPagado = now()->subMonths($mesesAtras)->format('Y-m');
                    
                    // Fecha de pago aleatoria en ese mes
                    $fechaPago = now()->subMonths($mesesAtras)
                        ->startOfMonth()
                        ->addDays(rand(1, 28));
                    
                    Pago::create([
                        'numero_recibo' => $generarNumeroRecibo(), // ✅ GENERAR NÚMERO DE RECIBO
                        'cliente_id' => $propiedad->cliente_id,
                        'propiedad_id' => $propiedad->id,
                        'monto' => $propiedad->tariff->precio_mensual,
                        'mes_pagado' => $mesPagado,
                        'fecha_pago' => $fechaPago,
                        'metodo' => Arr::random(['efectivo', 'transferencia']),
                        'comprobante' => fake()->boolean(50) ? 'COMP-' . fake()->randomNumber(6) : null,
                        'observaciones' => fake()->boolean(30) ? fake()->sentence() : null,
                        'registrado_por' => $cobradorId,
                    ]);
                    
                    // ✅ OPCIONAL: Marcar la deuda correspondiente como pagada
                    $deudaCorrespondiente = Debt::where('propiedad_id', $propiedad->id)
                        ->whereYear('fecha_emision', Carbon::parse($mesPagado)->year)
                        ->whereMonth('fecha_emision', Carbon::parse($mesPagado)->month)
                        ->first();
                        
                    if ($deudaCorrespondiente) {
                        $deudaCorrespondiente->update([
                            'estado' => 'pagada',
                            'monto_pendiente' => 0
                        ]);
                    }
                }
            });

        // === 8) ✅ PAGOS MÚLTIPLES DE EJEMPLO (cliente que paga varios meses) ===
        $propiedadEjemplo = Property::inRandomOrder()->first();
        if ($propiedadEjemplo) {
            $meses = [
                now()->format('Y-m'),
                now()->addMonth()->format('Y-m'),
                now()->addMonths(2)->format('Y-m'),
            ];
            
            foreach ($meses as $mes) {
                Pago::create([
                    'numero_recibo' => $generarNumeroRecibo(), // ✅ GENERAR NÚMERO DE RECIBO
                    'cliente_id' => $propiedadEjemplo->cliente_id,
                    'propiedad_id' => $propiedadEjemplo->id,
                    'monto' => $propiedadEjemplo->tariff->precio_mensual,
                    'mes_pagado' => $mes,
                    'fecha_pago' => now(),
                    'metodo' => 'efectivo',
                    'comprobante' => 'PAGO-ADELANTADO',
                    'observaciones' => 'Pago adelantado de varios meses',
                    'registrado_por' => $cobradorId,
                ]);
            }
        }

        $this->command->info('✅ Seeder ejecutado correctamente con pagos de ejemplo.');
    }
}