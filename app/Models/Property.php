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
// En App\Models\Property - REEMPLAZAR el método obtenerMesesAdeudados()
public function obtenerMesesAdeudados()
{
    try {
        \Log::info("🔍 Calculando meses adeudados para propiedad: {$this->id}");

        // Obtener TODOS los meses pagados para esta propiedad
        $mesesPagados = Pago::where('propiedad_id', $this->id)
            ->pluck('mes_pagado')
            ->toArray();

        \Log::info("💰 Meses pagados: " . json_encode($mesesPagados));

        // Obtener meses con deudas PENDIENTES
        $mesesConDeudaPendiente = Debt::where('propiedad_id', $this->id)
            ->where('estado', 'pendiente')
            ->where('monto_pendiente', '>', 0)
            ->get()
            ->map(function($deuda) {
                return $deuda->fecha_emision->format('Y-m');
            })
            ->toArray();

        \Log::info("📋 Meses con deuda pendiente: " . json_encode($mesesConDeudaPendiente));

        // ✅ CORRECCIÓN: Solo son meses adeudados los que tienen deuda pendiente
        // Y que no están pagados (por si hay inconsistencia)
        $mesesAdeudados = array_filter($mesesConDeudaPendiente, function($mes) use ($mesesPagados) {
            return !in_array($mes, $mesesPagados);
        });

        \Log::info("✅ Meses adeudados finales: " . json_encode($mesesAdeudados) . " - Total: " . count($mesesAdeudados));

        return array_values($mesesAdeudados); // Reindexar array

    } catch (\Exception $e) {
        \Log::error("💥 Error en obtenerMesesAdeudados para propiedad {$this->id}: " . $e->getMessage());
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
    