<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class deuda extends Model
{
    
    use HasFactory;

    protected $fillable = ['cliente_id', 'monto_pendiente', 'fecha_emision', 'fecha_vencimiento'];

    // Una deuda pertenece a un cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    // Una deuda tiene muchas multas
    public function multas()
    {
        return $this->hasMany(Multa::class);
    }
}
