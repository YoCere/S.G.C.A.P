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
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // === 1) PRIMERO: Crear roles y usuario admin ===
        $this->command->info('👤 Creando roles y usuario admin...');
        
        // Ejecutar RoleSeeder primero
        $this->call(RoleSeeder::class);
        
        // Crear usuario admin
        $adminEmail = 'josealfredocerezorios75@gmail.com';
        $admin = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador', 
                'password' => Hash::make('7516naBJ'),
                'email_verified_at' => now(),
            ]
        );
        
        // Asignar rol Admin
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }
        
        // === SOLO PARA DESARROLLO: Datos de prueba ===
        // Comenta esto si solo quieres el usuario admin
        if (app()->environment('local', 'staging') || env('SEED_TEST_DATA', false)) {
            $this->command->info('🚀 Generando datos de prueba...');
            $this->seedTestData($admin);
        }
        
        $this->command->info('');
        $this->command->info('🎉 SISTEMA CONFIGURADO!');
        $this->command->info('🔐 CREDENCIALES:');
        $this->command->info('   📧 Email: ' . $adminEmail);
        $this->command->info('   🔑 Password: 7516naBJ');
        $this->command->info('');
        $this->command->info('✅ ¡Puedes iniciar sesión ahora!');
    }
    
    private function seedTestData($admin)
    {
        $secretaria = User::factory()->create()->assignRole('Secretaria');
        $personalCorte = User::factory()->create()->assignRole('Operador');
        $empleados = collect([$admin, $secretaria, $personalCorte]);

        // === 2) Tarifas base ===
        $this->command->info('💰 Creando tarifas...');
        
        $tarifasBase = [
            ['nombre' => 'Normal',       'precio_mensual' => 40.00, 'descripcion' => 'Tarifa estándar'],
            ['nombre' => 'Adulto mayor', 'precio_mensual' => 25.00, 'descripcion' => 'Tarifa reducida'],
        ];
        foreach ($tarifasBase as $t) {
            Tariff::firstOrCreate(['nombre' => $t['nombre']], Arr::except($t, 'nombre'));
        }
        Tariff::factory()->count(3)->create();

        // === 3) Clientes ===
        $this->command->info('📝 Creando clientes...');
        
        $cantidadClientes = 30;
        for ($i = 0; $i < $cantidadClientes; $i++) {
            Client::create([
                'nombre' => fake()->name(),
                'ci' => fake()->unique()->numerify('########'),
                'telefono' => fake()->optional(0.8)->phoneNumber(),
                'estado_cuenta' => fake()->randomElement(['activo', 'inactivo']),
                'fecha_registro' => now(),
            ]);
        }

        // === 4) Propiedades ===
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

        // === 5) Deudas ===
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
                    $precio = optional($p->tariff)->precio_mensual ?? 0;

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
                                'monto_pendiente' => $precio,
                                'fecha_vencimiento' => $mes->copy()->endOfMonth(),
                                'estado' => $estado,
                            ]
                        );
                    }
                }
            });

        // === 6) Multas ===
        $this->command->info('⚡ Generando multas...');

        // Multas automáticas
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
                    'descripcion' => 'Multa automática por mora',
                    'fecha_aplicacion' => now(),
                    'estado' => Fine::ESTADO_PENDIENTE,
                    'aplicada_automaticamente' => true,
                    'activa' => true,
                    'creado_por' => $admin->id,
                ]);

                $deuda->propiedad->update(['estado' => 'corte_pendiente']);
            });

        // Pagos de ejemplo
        $this->command->info('💳 Generando pagos...');
        
        $cobradorId = $empleados->random()->id;
        $contadorRecibo = 1;

        Property::with(['client', 'tariff'])
            ->where('estado', 'activo')
            ->inRandomOrder()
            ->take(20)
            ->get()
            ->each(function ($propiedad) use ($cobradorId, &$contadorRecibo) {
                
                $deudasPendientes = $propiedad->debts()
                    ->where('estado', 'pendiente')
                    ->where('fecha_emision', '>=', now()->subMonths(6))
                    ->inRandomOrder()
                    ->take(rand(1, 3))
                    ->get();
                
                foreach ($deudasPendientes as $deuda) {
                    Pago::create([
                        'numero_recibo' => 'REC-' . str_pad($contadorRecibo++, 6, '0', STR_PAD_LEFT),
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
    }
}