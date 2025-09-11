<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Receipt;
use App\Models\Debt;
use App\Models\Fine;
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
        Client::factory(20)
            ->has(Receipt::factory(3)) // cada cliente con 3 recibos
            ->create()
            ->each(function ($cliente) {
                // para algunos recibos, crear deudas
                if ($cliente->recibos && $cliente->recibos->isNotEmpty()) {
                    $cliente->recibos->random(1)->each(function ($recibo) {
                        $deuda = Debt::factory()->create([
                            'cliente_id' => $recibo->cliente_id,
                        ]);

                    // a esa deuda le ponemos multas
                    Fine::factory(rand(0, 2))->create([
                        'deuda_id' => $deuda->id,
                    ]);
                });
                }
            });
            Client::factory(10)->create(); // Crea 20 clientes
            Debt::factory(10)->create();
    }
}