<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debt extends Model
{
    use HasFactory;

    // ✅ NUEVO: Constantes para estados de deuda
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_ANULADA = 'anulada';
    const ESTADO_CORTE_PENDIENTE = 'corte_pendiente';
    const ESTADO_CORTADO = 'cortado';

    protected $table = 'deudas';

    protected $fillable = [
        'propiedad_id',
        'tarifa_id',
        'monto_pendiente',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
        'pagada_adelantada',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'pagada_adelantada' => 'boolean',
    ];

    public function propiedad()
    {
        return $this->belongsTo(Property::class, 'propiedad_id');
    }

    public function tarifa()
    {
        return $this->belongsTo(Tariff::class, 'tarifa_id');
    }

    // NUEVO: recibos que han aplicado pagos a esta deuda
    public function recibos()
    {
        return $this->belongsToMany(Receipt::class, 'deuda_recibo', 'deuda_id', 'recibo_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }

    // Scopes útiles
    public function scopePendientes($q)
    {
        return $q->where('estado', self::ESTADO_PENDIENTE);
    }
    
    public function multas()
    {
        return $this->hasMany(Fine::class, 'deuda_id');
    }

    public function multasPendientes()
    {
        return $this->multas()->where('estado', Fine::ESTADO_PENDIENTE);
    }

    public function tieneMultasPendientes()
    {
        return $this->multasPendientes()->exists();
    }

    public function totalMultasPendientes()
    {
        return $this->multasPendientes()->sum('monto');
    }
}