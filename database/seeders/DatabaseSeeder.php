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
        $this->command->info('👤 Creando usuarios...');
        
        $admin = User::firstOrCreate(
            ['email' => 'josealfredocerezorios75@gmail.com'],
            ['name' => 'Administrador', 'password' => bcrypt('7516naBJ')]
        );

        $empleados = User::factory(4)->create();

        // === 2) Tarifas base + aleatorias ===
        $this->command->info('💰 Creando tarifas...');
        
        $tarifasBase = [
            ['nombre' => 'Normal',       'precio_mensual' => 40.00, 'descripcion' => 'Tarifa estándar para usuarios regulares'],
            ['nombre' => 'Adulto mayor', 'precio_mensual' => 25.00, 'descripcion' => 'Tarifa reducida para adultos mayores'],
        ];
        foreach ($tarifasBase as $t) {
            Tariff::firstOrCreate(['nombre' => $t['nombre']], Arr::except($t, 'nombre'));
        }
        Tariff::factory()->count(3)->create();

        // === 3) Clientes - CORREGIDO: Crear uno por uno ===
        $this->command->info('📝 Creando clientes con códigos aleatorios...');
        
        $cantidadClientes = 30;
        
        // Crear clientes uno por uno para que se generen códigos automáticamente
        for ($i = 0; $i < $cantidadClientes; $i++) {
            Client::create([
                'nombre' => fake()->name(),
                'ci' => fake()->unique()->numerify('########'),
                'telefono' => fake()->optional(0.8)->phoneNumber(),
                'estado_cuenta' => fake()->randomElement(['activo', 'inactivo']),
                'fecha_registro' => now(),
            ]);
        }

        $this->command->info("✅ {$cantidadClientes} clientes creados con códigos aleatorios únicos");

        // === 4) Propiedades (1–2 por cliente) con tarifa asignada ===
        $this->command->info('🏠 Creando propiedades...');
        
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
        $this->command->info('💰 Generando deudas...');
        
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

        // === 6) ✅ MULTAS AUTOMÁTICAS Y MANUALES ===
        $this->command->info('⚡ Generando multas...');
        
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

                if (in_array($tipo, [Fine::TIPO_CONEXION_CLANDESTINA, Fine::TIPO_MANIPULACION_LLAVES])) {
                    $propiedad->update(['estado' => 'cortado']);
                    $propiedad->debts()
                        ->where('estado', 'corte_pendiente')
                        ->update(['estado' => 'cortado']);
                }
            });

        // === 7) ✅ PAGOS DE EJEMPLO ===
        $this->command->info('💳 Generando pagos...');
        
        $cobradorId = $empleados->random()->id ?? $admin->id;

        $generarNumeroRecibo = function() {
            static $contador = 1;
            return 'REC-' . str_pad($contador++, 6, '0', STR_PAD_LEFT);
        };

        // Pagos para propiedades activas
        Property::with(['client', 'tariff'])
            ->where('estado', 'activo')
            ->inRandomOrder()
            ->take(20)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, $generarNumeroRecibo) {
                
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
                    
                    $deuda->update([
                        'estado' => 'pagada',
                        'monto_pendiente' => 0
                    ]);
                }
            });

        // === 8) ✅ PAGOS CON MULTAS ===
        Property::where('estado', 'cortado')
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, $generarNumeroRecibo) {
                
                $deudasCortadas = $propiedad->debts()
                    ->where('estado', 'cortado')
                    ->get();
                
                $multasPendientes = $propiedad->multas()
                    ->where('estado', Fine::ESTADO_PENDIENTE)
                    ->get();
                
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
                
                foreach ($multasPendientes as $multa) {
                    $multa->update(['estado' => Fine::ESTADO_PAGADA]);
                }
                
                $propiedad->update(['estado' => 'activo']);
            });

        // === 9) ✅ PAGOS ADELANTADOS ===
        $propiedadEjemplo = Property::where('estado', 'activo')->inRandomOrder()->first();
        if ($propiedadEjemplo) {
            $mesesAdelanto = [
                now()->format('Y-m'),
                now()->addMonth()->format('Y-m'),
                now()->addMonths(2)->format('Y-m'),
            ];
            
            foreach ($mesesAdelanto as $mes) {
                $fechaEmision = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                $deuda = Debt::firstOrCreate(
                    ['propiedad_id' => $propiedadEjemplo->id, 'fecha_emision' => $fechaEmision],
                    [
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

        // === 10) ✅ MOSTRAR RESULTADOS ===
        $this->command->info('');
        $this->command->info('🎉 SISTEMA INICIALIZADO CORRECTAMENTE');
        $this->command->info('=====================================');
        
        $this->command->info('📋 Códigos de cliente generados (aleatorios):');
        $primerosClientes = Client::take(5)->get();
        foreach ($primerosClientes as $cliente) {
            $this->command->info("   👤 {$cliente->codigo_cliente} - {$cliente->nombre}");
        }
        
        if (Client::count() > 5) {
            $this->command->info("   ... y " . (Client::count() - 5) . " clientes más");
        }

        $this->command->info('');
        $this->command->info('📊 ESTADÍSTICAS DEL SISTEMA:');
        $this->command->info('   👥 Clientes: ' . Client::count());
        $this->command->info('   🏠 Propiedades: ' . Property::count());
        $this->command->info('   💰 Tarifas: ' . Tariff::count());
        $this->command->info('   📋 Deudas: ' . Debt::count());
        $this->command->info('   ⚡ Multas: ' . Fine::count());
        $this->command->info('   💳 Pagos: ' . Pago::count());
        
        $this->command->info('');
        $this->command->info('🔐 CREDENCIALES DE ACCESO:');
        $this->command->info('   📧 Email: josealfredocerezorios75@gmail.com');
        $this->command->info('   🔑 Password: 7516naBJ');
        $this->command->info('');
        $this->command->info('✅ ¡El sistema está listo para usar!');
    }
}