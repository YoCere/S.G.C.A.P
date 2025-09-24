<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;


class Tariff extends Model
{
    use HasFactory, softDeletes;

    protected $table = 'tarifas';

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
