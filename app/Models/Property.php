<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pago;

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

    
    // Agregar este método al modelo Property
    public function obtenerMesesAdeudados()
    {
        // Obtener todos los pagos de esta propiedad
        $pagos = Pago::where('propiedad_id', $this->id)->get();
        $mesesPagados = $pagos->pluck('mes_pagado')->toArray();
        
        // Generar los últimos 12 meses
        $mesesAdeudados = [];
        $fechaActual = now();
        
        for ($i = 11; $i >= 0; $i--) {
            $mes = $fechaActual->copy()->subMonths($i)->format('Y-m');
            
            // Si el mes no está pagado, agregarlo a adeudados
            if (!in_array($mes, $mesesPagados)) {
                $mesesAdeudados[] = $mes;
            }
        }
        
        return $mesesAdeudados;
    }

    public function getTotalDeudasPendientesAttribute()
    {
        return $this->debts()->where('estado', 'pendiente')->sum('monto_pendiente');
    }
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
    