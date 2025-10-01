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
        $lat = $this->faker->randomFloat(8, -21.935, -21.930);
        $lng = $this->faker->randomFloat(8, -63.637, -63.632);

        // Opciones de atributos
        $colores = ['verde', 'azul', 'rojo', 'amarillo', 'blanco', 'gris', 'marrón', 'celeste', 'beige', 'naranja'];
        $detalles = [
            'casa de 1 piso',
            'casa de 2 pisos',
            'casa pequeña',
            'casa grande',
            'casa de esquina',
        ];
        $portones = [
            'sin portón',
            'con portón negro',
            'con portón rojo',
            'con portón gris',
            'con portón de madera',
        ];
        
        // ✅ NUEVO: Barrios para el factory
        $barrios = ['Centro', 
               'Aroma', 
               'Los Valles', 
               'Caipitandy', 
               'Primavera',
               'Arboleda'];

        // Construcción de la referencia
        $referencia = $this->faker->randomElement($detalles) 
                    . ' color ' . $this->faker->randomElement($colores) 
                    . ' ' . $this->faker->randomElement($portones);

        return [
            'cliente_id' => Client::inRandomOrder()->value('id') ?? Client::factory(), // ← AGREGADO cliente_id
            'tarifa_id'  => Tariff::inRandomOrder()->value('id') ?? Tariff::factory(),
            'referencia' => ucfirst($referencia),
            'barrio'     => $this->faker->randomElement($barrios), // ✅ NUEVO
            'latitud'    => $lat,
            'longitud'   => $lng,
            'estado'     => $this->faker->randomElement(['activo', 'inactivo']),
        ];
    }
}