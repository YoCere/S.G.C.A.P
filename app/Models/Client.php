<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;
    protected $table = 'clientes'; // tabla en español
    protected $fillable = ['nombre','ci','telefono','estado_cuenta','fecha_registro'];

    public function properties(){ return $this->hasMany(Property::class, 'cliente_id'); }
    public function receipts(){ return $this->hasMany(Receipt::class, 'cliente_id'); }
    public function debts(){
        return $this->hasManyThrough(
            Debt::class,     // deudas
            Property::class, // propiedades
            'cliente_id',    // FK en propiedades -> clientes.id
            'propiedad_id',  // FK en deudas -> propiedades.id
            'id',            // PK clientes
            'id'             // PK propiedades
        );
    }
    public function Receipt()
    {
        return $this->hasMany(Receipt::class, 'cliente_id');
    }

    // Un cliente tiene muchas deudas
   
    public function multa()
    {
        return $this->hasMany(Fine::class);
    }

   
}
