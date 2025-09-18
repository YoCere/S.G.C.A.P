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

        
    }
}
