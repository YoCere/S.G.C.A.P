<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'numero_recibo',
        'propiedad_id', 
        'monto',
        'mes_pagado',
        'fecha_pago',
        'metodo',
        'comprobante',
        'observaciones',
        'registrado_por'
    ];

    protected $casts = [
        'fecha_pago' => 'date',
        'monto' => 'decimal:2'
    ];

    // ✅ CORREGIDO: Eliminar relación cliente() que no existe
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'propiedad_id');
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // ✅ CORREGIDO: Especificar claves foráneas explícitamente
    public function multasPagadas(): BelongsToMany
    {
        return $this->belongsToMany(Fine::class, 'multa_pago', 'pago_id', 'multa_id')
                    ->withPivot('monto_pagado')
                    ->withTimestamps();
    }

    // ✅ ACCESOR para obtener cliente a través de propiedad
    public function getClienteAttribute()
    {
        return $this->propiedad->client;
    }

    public function getMesPagadoFormateadoAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->mes_pagado)
                            ->locale('es')
                            ->translatedFormat('F Y');
    }

    // ✅ NUEVO: Calcular total incluyendo multas
    public function getTotalConMultasAttribute()
    {
        $totalMultas = $this->multasPagadas->sum('pivot.monto_pagado');
        return $this->monto + $totalMultas;
    }

    // ✅ NUEVO: Obtener todas las multas pagadas en este recibo
    public function obtenerMultasDelRecibo()
    {
        return Pago::where('numero_recibo', $this->numero_recibo)
            ->with('multasPagadas')
            ->get()
            ->pluck('multasPagadas')
            ->flatten()
            ->unique('id');
    }
}