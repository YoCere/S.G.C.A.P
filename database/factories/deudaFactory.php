<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Deuda;
use App\Models\Cliente;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class deudaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {   
        return [
            'cliente_id' => Cliente::inRandomOrder()->first()->id ?? Cliente::factory(),
            'monto_pendiente' => $this->faker->randomFloat(2, 20, 300),
            'fecha_emision' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'fecha_vencimiento' => $this->faker->dateTimeBetween('now', '+1 month'),
            'estado' => $this->faker->randomElement(['pendiente', 'pagada', 'vencida']),
        ];
    }
}
