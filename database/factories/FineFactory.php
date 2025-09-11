<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Multa>
 */
class FineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'deuda_id' => \App\Models\Debt::inRandomOrder()->first()->id ?? \App\Models\Debt::factory(),
            'monto' => $this->faker->randomFloat(2, 5, 100),
            'descripcion' => $this->faker->sentence,
            'fecha' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
