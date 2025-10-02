<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Client extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';
    protected $fillable = ['nombre', 'ci', 'telefono', 'estado_cuenta', 'fecha_registro'];

    // ✅ AGREGAR CASTS PARA FECHAS
    protected $casts = [
        'fecha_registro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function properties()
    { 
        return $this->hasMany(Property::class, 'cliente_id'); 
    }

    public function debt()
    { 
        return $this->hasMany(Debt::class, 'deuda_id'); 
    }
    
    public function receipts()
    { 
        return $this->hasMany(Receipt::class, 'cliente_id'); 
    }

    public function multas()
    {
        return $this->hasMany(Fine::class, 'cliente_id');
    }

    // ✅ MÉTODO ACCESOR PARA FECHA SEGURO
    public function getFechaRegistroFormateadaAttribute()
    {
        try {
            return Carbon::parse($this->fecha_registro)->format('d/m/Y');
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }
}