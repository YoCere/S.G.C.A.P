<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    protected $table = 'multas';
    use HasFactory;

    protected $fillable = ['deuda_id', 'monto', 'descripcion', 'fecha'];

    // Una multa pertenece a una deuda
    public function deuda()
    {
        return $this->belongsTo(Debt::class);
    }
}
