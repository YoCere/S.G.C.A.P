<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use App\Models\client;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ReceiptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    
        return [
            'emitido' => $this->faker->dateTimeBetween('-1 month', 'now'),
            
            'periodo_facturado' => $this->faker->dateTimeBetween('-2 months', 'now'),
            'monto_total' => $this->faker->randomFloat(2, 50, 500),
            'monto_multa' => $this->faker->optional()->randomFloat(2, 0, 100) ?? 0,
            'user_id' => \App\Models\User::factory(), // Genera un usuario relacionado
        ];
    }
}
