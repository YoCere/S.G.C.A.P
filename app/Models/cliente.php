<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cliente extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'direccion', 'telefono', 'estado_cuenta', 'fecha_registro'];

    // Un cliente tiene muchos recibos
    public function recibos()
    {
        return $this->hasMany(Recibo::class);
    }

    // Un cliente tiene muchas deudas
    public function deudas()
    {
        return $this->hasMany(Deuda::class);
    }

    public function multa()
    {
        return $this->hasMany(Multa::class);
    }
}
