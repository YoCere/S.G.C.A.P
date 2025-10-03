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

        // === 5) Deudas para cada propiedad (últimos 6 meses) ===
        $meses = [];
        for ($i = 5; $i >= 0; $i--) {
            $meses[] = now()->startOfMonth()->subMonths($i);
        }

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
                    // Determinar estado basado en antigüedad (más realista)
                    $diasTranscurridos = now()->diffInDays($mes);
                    $estado = 'pendiente';
                    
                    if ($diasTranscurridos > 90) {
                        $estado = 'corte_pendiente';
                    } elseif ($diasTranscurridos > 30) {
                        $estado = fake()->boolean(70) ? 'vencida' : 'pendiente';
                    }

                    Debt::firstOrCreate(
                        ['propiedad_id' => $p->id, 'fecha_emision' => $mes],
                        [
                            // ✅ REMOVIDO TEMPORALMENTE: 'cliente_id' => $p->cliente_id,
                            'tarifa_id' => $tarifaId,
                            'monto_pendiente' => $precio,
                            'fecha_vencimiento' => $mes->copy()->addDays(30),
                            'estado' => $estado,
                            'pagada_adelantada' => false,
                        ]
                    );
                }
            }
        });
        
        $tarifaFallback = Tariff::inRandomOrder()->value('id') ?? Tariff::first()->id;
        Property::whereNull('tarifa_id')->update(['tarifa_id' => $tarifaFallback]);

        // === 6) ✅ ACTUALIZADO: MULTAS CON NUEVA ESTRUCTURA ===
        // Multas automáticas por reconexión
        Debt::where('estado', 'corte_pendiente')
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->each(function ($deuda) use ($admin) {
                $mesesMora = now()->diffInMonths($deuda->fecha_vencimiento);
                $tipoMulta = $mesesMora >= 12 ? 
                    Fine::TIPO_RECONEXION_12MESES : 
                    Fine::TIPO_RECONEXION_3MESES;

                Fine::create([
                    'deuda_id' => $deuda->id,
                    'propiedad_id' => $deuda->propiedad_id,
                    'tipo' => $tipoMulta,
                    'nombre' => Fine::obtenerTiposMulta()[$tipoMulta],
                    'monto' => Fine::obtenerMontosBase()[$tipoMulta],
                    'descripcion' => 'Multa aplicada automáticamente por ' . ($mesesMora >= 12 ? '12' : '3') . ' meses de mora',
                    'fecha_aplicacion' => now(),
                    'estado' => Fine::ESTADO_PENDIENTE,
                    'aplicada_automaticamente' => true,
                    'activa' => true,
                    'creado_por' => $admin->id,
                ]);

                // Actualizar estado de la propiedad
                $deuda->propiedad->update(['estado' => 'corte_pendiente']);
            });

        // Multas manuales por infracciones
        $tiposManuales = [
            Fine::TIPO_CONEXION_CLANDESTINA,
            Fine::TIPO_MANIPULACION_LLAVES,
            Fine::TIPO_CONSTRUCCION,
            Fine::TIPO_OTRO
        ];

        Property::inRandomOrder()
            ->take(15)
            ->get()
            ->each(function ($propiedad) use ($tiposManuales, $empleados, $admin) {
                $tipo = Arr::random($tiposManuales);
                
                Fine::create([
                    'propiedad_id' => $propiedad->id,
                    'tipo' => $tipo,
                    'nombre' => Fine::obtenerTiposMulta()[$tipo],
                    'monto' => Fine::obtenerMontosBase()[$tipo],
                    'descripcion' => 'Multa aplicada por: ' . fake()->sentence(),
                    'fecha_aplicacion' => now()->subDays(rand(1, 60)),
                    'estado' => Arr::random([Fine::ESTADO_PENDIENTE, Fine::ESTADO_PAGADA]),
                    'aplicada_automaticamente' => false,
                    'activa' => true,
                    'creado_por' => $empleados->random()->id ?? $admin->id,
                ]);

                // Si es multa grave, cortar la propiedad
                if (in_array($tipo, [Fine::TIPO_CONEXION_CLANDESTINA, Fine::TIPO_MANIPULACION_LLAVES])) {
                    $propiedad->update(['estado' => 'cortado']);
                    
                    // También actualizar deudas a estado cortado
                    $propiedad->debts()
                        ->where('estado', 'corte_pendiente')
                        ->update(['estado' => 'cortado']);
                }
            });

        // === 7) ✅ ACTUALIZADO: PAGOS DE EJEMPLO CON NUEVA LÓGICA ===
        $cobradorId = $empleados->random()->id ?? $admin->id;

        // Función para generar número de recibo único
        $generarNumeroRecibo = function() {
            static $contador = 1;
            return 'REC-' . str_pad($contador++, 6, '0', STR_PAD_LEFT);
        };

        // Pagos para propiedades sin multas pendientes
        Property::with(['client', 'tariff'])
            ->where('estado', 'activo')
            ->inRandomOrder()
            ->take(20)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, $generarNumeroRecibo) {
                
                // Obtener deudas pendientes de los últimos 6 meses
                $deudasPendientes = $propiedad->debts()
                    ->where('estado', 'pendiente')
                    ->where('fecha_emision', '>=', now()->subMonths(6))
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->get();
                
                foreach ($deudasPendientes as $deuda) {
                    Pago::create([
                        'numero_recibo' => $generarNumeroRecibo(),
                        'cliente_id' => $propiedad->cliente_id,
                        'propiedad_id' => $propiedad->id,
                        'monto' => $deuda->monto_pendiente,
                        'mes_pagado' => $deuda->fecha_emision->format('Y-m'),
                        'fecha_pago' => now()->subDays(rand(1, 30)),
                        'metodo' => Arr::random(['efectivo', 'transferencia', 'qr']),
                        'comprobante' => fake()->boolean(50) ? 'COMP-' . fake()->randomNumber(6) : null,
                        'observaciones' => fake()->boolean(30) ? fake()->sentence() : null,
                        'registrado_por' => $cobradorId,
                    ]);
                    
                    // Actualizar deuda
                    $deuda->update([
                        'estado' => 'pagada',
                        'monto_pendiente' => 0
                    ]);
                }
            });

        // === 8) ✅ NUEVO: PAGOS CON MULTAS (propiedades cortadas) ===
        Property::where('estado', 'cortado')
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, $generarNumeroRecibo) {
                
                // Obtener deudas cortadas y multas pendientes
                $deudasCortadas = $propiedad->debts()
                    ->where('estado', 'cortado')
                    ->get();
                
                $multasPendientes = $propiedad->multas()
                    ->where('estado', Fine::ESTADO_PENDIENTE)
                    ->get();
                
                // Pagar deudas cortadas
                foreach ($deudasCortadas as $deuda) {
                    Pago::create([
                        'numero_recibo' => $generarNumeroRecibo(),
                        'cliente_id' => $propiedad->cliente_id,
                        'propiedad_id' => $propiedad->id,
                        'monto' => $deuda->monto_pendiente,
                        'mes_pagado' => $deuda->fecha_emision->format('Y-m'),
                        'fecha_pago' => now(),
                        'metodo' => 'efectivo',
                        'comprobante' => 'PAGO-RECONEXION',
                        'observaciones' => 'Pago incluye deuda cortada',
                        'registrado_por' => $cobradorId,
                    ]);
                    
                    $deuda->update([
                        'estado' => 'pagada',
                        'monto_pendiente' => 0
                    ]);
                }
                
                // Marcar multas como pagadas
                foreach ($multasPendientes as $multa) {
                    $multa->update(['estado' => Fine::ESTADO_PAGADA]);
                }
                
                // Restaurar propiedad
                $propiedad->update(['estado' => 'activo']);
            });

        // === 9) ✅ NUEVO: PAGOS MÚLTIPLES CON ADELANTOS ===
        $propiedadEjemplo = Property::where('estado', 'activo')->inRandomOrder()->first();
        if ($propiedadEjemplo) {
            $mesesAdelanto = [
                now()->format('Y-m'),
                now()->addMonth()->format('Y-m'),
                now()->addMonths(2)->format('Y-m'),
            ];
            
            foreach ($mesesAdelanto as $mes) {
                // Crear deuda adelantada si no existe
                $fechaEmision = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                $deuda = Debt::firstOrCreate(
                    ['propiedad_id' => $propiedadEjemplo->id, 'fecha_emision' => $fechaEmision],
                    [
                        // ✅ REMOVIDO TEMPORALMENTE: 'cliente_id' => $propiedadEjemplo->cliente_id,
                        'tarifa_id' => $propiedadEjemplo->tarifa_id,
                        'monto_pendiente' => $propiedadEjemplo->tariff->precio_mensual,
                        'fecha_vencimiento' => $fechaEmision->copy()->addDays(30),
                        'estado' => 'pendiente',
                        'pagada_adelantada' => true,
                    ]
                );
                
                Pago::create([
                    'numero_recibo' => $generarNumeroRecibo(),
                    'cliente_id' => $propiedadEjemplo->cliente_id,
                    'propiedad_id' => $propiedadEjemplo->id,
                    'monto' => $propiedadEjemplo->tariff->precio_mensual,
                    'mes_pagado' => $mes,
                    'fecha_pago' => now(),
                    'metodo' => 'transferencia',
                    'comprobante' => 'ADELANTO-' . fake()->randomNumber(6),
                    'observaciones' => 'Pago adelantado de varios meses',
                    'registrado_por' => $cobradorId,
                ]);
                
                $deuda->update([
                    'estado' => 'pagada',
                    'monto_pendiente' => 0
                ]);
            }
        }

        $this->command->info('✅ Seeder ejecutado correctamente con el nuevo sistema de multas y cortes.');
        $this->command->info('📊 Estadísticas generadas:');
        $this->command->info('   - Propiedades activas: ' . Property::where('estado', 'activo')->count());
        $this->command->info('   - Propiedades corte pendiente: ' . Property::where('estado', 'corte_pendiente')->count());
        $this->command->info('   - Propiedades cortadas: ' . Property::where('estado', 'cortado')->count());
        $this->command->info('   - Multas generadas: ' . Fine::count());
        $this->command->info('   - Multas automáticas: ' . Fine::where('aplicada_automaticamente', true)->count());
        $this->command->info('   - Multas pendientes: ' . Fine::where('estado', Fine::ESTADO_PENDIENTE)->count());
    }
}