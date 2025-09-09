<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ClientesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'nombre' => $this->faker->unique()->name,
            'telefono' => $this->faker->numerify('########'), // 8 dígitos
            'referencia' => $this->faker->address,
            'latitud' => $this->faker->latitude(-22.1, -21.9), // Yacuiba aprox
            'longitud' => $this->faker->longitude(-63.8, -63.6),
            'estado_cuenta' => $this->faker->randomElement(['activo', 'inactivo', 'deudor']),
            'fecha_registro' => $this->faker->dateTimeBetween('-2 years', 'now')

        ];
    }
}
