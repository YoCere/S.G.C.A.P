<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'properties';

    protected $fillable = [
        'cliente_id',
        'tarifa_id',
        'referencia',
        'latitud',
        'longitud',
        'estado',
    ];

    // Relaciones
    public function client()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class, 'tarifa_id');
    }

    public function debts()
    {
        return $this->hasMany(Debt::class, 'propiedad_id'); // si usas deudas con FK propiedad_id
    }

    // Scopes útiles
    public function scopeActivas($q)
    {
        return $q->where('estado', 'activo');
    }
}
