<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Receipt;
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
        ->whereNotNull('tarifa_id')                     // <- evita propiedades sin tarifa
        ->with('tariff:id,precio_mensual')              // <- eager carga la tarifa
        ->chunkById(200, function ($props) use ($meses) {
            foreach ($props as $p) {
                // fallback extra (por si acaso)
                $tarifaId = $p->tarifa_id ?: Tariff::inRandomOrder()->value('id');
                if (!$tarifaId) { continue; } // si aun así no hay, salta

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

        // === 7) Recibos de ejemplo: cancelar 1–2 deudas por algunos clientes ===
        $cobradorId = $empleados->random()->id ?? $admin->id;
        $cobradorId = $empleados->random()->id ?? $admin->id;

        Client::with(['properties.debts' => fn($q) => $q->whereIn('estado', ['pendiente','vencida'])])
            ->inRandomOrder()
            ->take(10)
            ->get()
            ->each(function ($cliente) use ($cobradorId) {
                $deudas = $cliente->properties->flatMap->debts->take(2);
                if ($deudas->isEmpty()) return;
        
                DB::transaction(function () use ($cliente, $deudas, $cobradorId) {
                    $total = 0.0;
                    $pivot = [];
        
                    foreach ($deudas as $d) {
                        $monto = (float) $d->monto_pendiente;
                        $total += $monto;
                        $pivot[$d->id] = ['monto_aplicado' => $monto];
        
                        $d->update(['estado' => 'pagada', 'monto_pendiente' => 0]);
                    }
        
                    $recibo = \App\Models\Receipt::create([
                        'cliente_id'        => $cliente->id,
                        'user_id'           => $cobradorId,
                        'periodo_facturado' => now()->startOfMonth(),
                        'monto_total'       => $total,
                        'monto_multa'       => 0,
                        'referencia'        => 'Pago automático de seeder',
                    ]);
        
                    $recibo->deudas()->attach($pivot);
                });
            });
        
    }
}
