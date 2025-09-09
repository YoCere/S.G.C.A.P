<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Monolog\Formatter\LineFormatter;
use App\Models\cliente;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class recibosFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    
        return [
            'emitido'=>$this->faker->date(),
            'cliente_id'=> cliente::all()->random()->id,
            'user_id'=> cliente::all()->random()->id,
            'periodo_facturado'=>$this->faker->date(),
            'monto_total'=>$this->faker->randomFloat(2, 50, 500),
            'monto_multa'=>$this->faker->randomFloat(2, 0, 100),
            'created_at'=>$this->faker->date(),
            'updated_at'=>$this->faker->date()
        ];
    }
}
