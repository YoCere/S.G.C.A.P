<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Tariff;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        // Coordenadas aproximadas de Yacuiba y alrededores
        $lat = $this->faker->randomFloat(8, -22.15, -21.90);
        $lng = $this->faker->randomFloat(8, -63.90, -63.50);

        return [
            'cliente_id' => Client::inRandomOrder()->value('id') ?? Client::factory(),
            'tarifa_id'  => Tariff::inRandomOrder()->value('id') ?? Tariff::factory(),
            'referencia' => 'Casa ' . $this->faker->streetName().' #'.$this->faker->buildingNumber(),
            'latitud'    => $lat,
            'longitud'   => $lng,
            'estado'     => $this->faker->randomElement(['activo','inactivo']),
        ];
    }
}