<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Multa>
 */
class multaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deuda_id' => \App\Models\Deuda::inRandomOrder()->first()->id ?? \App\Models\Deuda::factory(),
            'monto' => $this->faker->randomFloat(2, 5, 100),
            'descripcion' => $this->faker->sentence,
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
