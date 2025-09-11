<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $table = 'recibos';
    use HasFactory;

    protected $fillable = ['cliente_id', 'usuario_id', 'periodo_facturado', 'monto_total', 'monto_multa'];

    // Un recibo pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Client::class);
    }

    // Un recibo pertenece a un usuario (quien lo emitió)
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
}
