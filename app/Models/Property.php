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
    // En Property.php, reemplazar el método obtenerMesesAdeudados:
public function obtenerMesesAdeudados()
{
    try {
        // Obtener meses ya pagados
        $mesesPagados = Pago::where('propiedad_id', $this->id)
            ->pluck('mes_pagado')
            ->toArray();

        // Generar los últimos 12 meses + año actual completo
        $mesesAdeudados = [];
        $fechaInicio = now()->subMonths(12)->startOfMonth();
        $fechaFin = now()->endOfYear();
        
        $fechaActual = $fechaInicio->copy();
        while ($fechaActual <= $fechaFin) {
            $mes = $fechaActual->format('Y-m');
            
            // Si el mes no está pagado, agregarlo a adeudados
            if (!in_array($mes, $mesesPagados)) {
                $mesesAdeudados[] = $mes;
            }
            
            $fechaActual->addMonth();
        }
        
        return $mesesAdeudados;

    } catch (\Exception $e) {
        \Log::error("Error en obtenerMesesAdeudados para propiedad {$this->id}: " . $e->getMessage());
        return [];
    }
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
    // En tu modelo Property, agregar:
    public function multas()
    {
        return $this->hasMany(Fine::class, 'propiedad_id');
    }

    public function multasPendientes()
    {
        return $this->multas()->where('estado', Fine::ESTADO_PENDIENTE);
    }
}
    