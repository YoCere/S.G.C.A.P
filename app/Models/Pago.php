<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'numero_recibo', // ✅ AHORA EXISTE EN LA BD
        'cliente_id',
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

    // ✅ RELACIONES DIRECTAS
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }

    // ✅ ELIMINADO: Accessor getNumeroReciboAttribute (ya es campo real)

    public function getMesPagadoFormateadoAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m', $this->mes_pagado)
                            ->format('F Y');
    }
}