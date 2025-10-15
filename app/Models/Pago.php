<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}