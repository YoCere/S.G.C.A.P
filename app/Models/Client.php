<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clientes';
    use HasFactory;
    protected $fillable = ['nombre', 'ci', 'direccion', 'telefono', 'estado_cuenta', 'fecha_registro'];

    // Un cliente tiene muchos recibos
    public function Receipt()
    {
        return $this->hasMany(Receipt::class, 'cliente_id');
    }

    // Un cliente tiene muchas deudas
    public function Debt()
    {
        return $this->hasMany(Debt::class);
    }

    public function multa()
    {
        return $this->hasMany(Fine::class);
    }

   
}
