<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tariff extends Model
{
    use HasFactory;

    protected $table = 'tariffs';

    protected $fillable = [
        'nombre',
        'precio_mensual',
        'descripcion',
    ];

    // Relaciones
    public function properties()
    {
        return $this->hasMany(Property::class, 'tarifa_id');
    }
}
