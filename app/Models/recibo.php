<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class recibo extends Model
{
    use HasFactory;

    protected $fillable = ['cliente_id', 'usuario_id', 'periodo_facturado', 'monto_total', 'monto_multa'];

    // Un recibo pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Un recibo pertenece a un usuario (quien lo emitió)
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
