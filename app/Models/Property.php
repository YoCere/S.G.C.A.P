<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $table = 'propiedades';

    protected $fillable = [
        'cliente_id',
        'tarifa_id',
        'referencia',
        'barrio',
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
        return $this->belongsTo(\App\Models\Tariff::class, 'tarifa_id')->withTrashed();
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
    public function getClienteNombreAttribute()
    {
        return $this->cliente ? $this->cliente->nombre : 'Cliente No Asignado';
    }
}
    