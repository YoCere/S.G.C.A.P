<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Cliente;
use App\Models\Recibo;
use App\Models\Deuda;
use App\Models\Multa;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario admin fijo
        User::firstOrCreate(
            ['email' => 'josealfredocerezorios75@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => bcrypt('7516naBJ'),
            ]
        );

        // Empleados random
        User::factory(4)->create();

        // Clientes con recibos
        Cliente::factory(20)
            ->has(Recibo::factory(3)) // cada cliente con 3 recibos
            ->create()
            ->each(function ($cliente) {
                // para algunos recibos, crear deudas
                $cliente->recibos->random(1)->each(function ($recibo) {
                    $deuda = Deuda::factory()->create([
                        'cliente_id' => $recibo->cliente_id,
                        // puedes añadir recibo_id si lo tienes
                    ]);

                    // a esa deuda le ponemos multas
                    Multa::factory(rand(0, 2))->create([
                        'deuda_id' => $deuda->id,
                    ]);
                });
            });
    }
}