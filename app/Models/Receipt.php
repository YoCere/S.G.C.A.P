<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $table = 'recibos';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'periodo_facturado',
        'monto_total',
        'monto_multa',
        'referencia',
    ];

    protected $casts = [
        'periodo_facturado' => 'date',
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // NUEVO: detalle de deudas pagadas en este recibo
    public function deudas()
    {
        return $this->belongsToMany(Debt::class, 'deuda_recibo', 'recibo_id', 'deuda_id')
            ->withPivot('monto_aplicado')
            ->withTimestamps();
    }
}
