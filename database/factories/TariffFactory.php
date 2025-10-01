<?php
namespace Database\Factories;

use App\Models\Tariff;
use Illuminate\Database\Eloquent\Factories\Factory;

class TariffFactory extends Factory
{
    protected $model = Tariff::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->randomElement(['Normal', 'Adulto mayor', 'Comercial', 'Industrial']),
            'precio_mensual' => $this->faker->randomFloat(2, 10, 100),
            'descripcion' => $this->faker->sentence(),
            'activo' => $this->faker->boolean(80), 
        ];
    }
}
