<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'nombre'         => $this->faker->name(),
            'ci'             => $this->faker->unique()->numerify('1#######'),
            'telefono'       => $this->faker->optional()->numerify('7#######'),
            'estado_cuenta'  => $this->faker->randomElement(['activo','inactivo','deudor']),
            'fecha_registro' => now()->toDateString(),
        ];
    }
}
