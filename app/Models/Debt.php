<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
    protected $table = 'deudas';
    use HasFactory;

    protected $fillable = ['cliente_id', 'monto_pendiente', 'fecha_emision', 'fecha_vencimiento'];

    // Una deuda pertenece a un cliente
    public function Client()
    {
        return $this->belongsTo(Client::class);
    }

    // Una deuda tiene muchas multas
    public function Fine()
    {
        return $this->hasMany(Fine::class);
    }
}
