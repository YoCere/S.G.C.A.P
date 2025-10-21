<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use HasFactory;
    
    protected $table = 'clientes';
    
    protected $fillable = [
        'codigo_cliente',
        'nombre', 
        'ci', 
        'telefono', 
        'estado_cuenta', 
        'fecha_registro'
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ✅ CONSTANTES PARA ESTADOS
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($client) {
            if (!$client->codigo_cliente) {
                $client->codigo_cliente = self::generarCodigoAleatorioUnico();
            }
            // ✅ Establecer estado activo por defecto
            if (!$client->estado_cuenta) {
                $client->estado_cuenta = self::ESTADO_ACTIVO;
            }
        });
    }

    // ✅ GENERAR CÓDIGO ALEATORIO ÚNICO Y SEGURO
    public static function generarCodigoAleatorioUnico()
    {
        $maxIntentos = 10;
        $intento = 0;
        
        do {
            $codigo = str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
            $intento++;
            
            // Verificar si existe en la base de datos
            $existe = DB::table('clientes')->where('codigo_cliente', $codigo)->exists();
            
            if (!$existe) {
                return $codigo;
            }
            
            // Si hemos intentado demasiadas veces, usar timestamp
            if ($intento >= $maxIntentos) {
                return self::generarCodigoDeReserva();
            }
            
        } while (true);
    }

    // ✅ CÓDIGO DE RESERVA EN CASO EXTREMO
    private static function generarCodigoDeReserva()
    {
        do {
            // Usar microtime para garantizar unicidad
            $codigo = substr(str_replace('.', '', microtime(true)), -5);
            $codigo = str_pad($codigo, 5, '0', STR_PAD_LEFT);
        } while (DB::table('clientes')->where('codigo_cliente', $codigo)->exists());
        
        return $codigo;
    }

    // Relaciones (mantener igual)
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

    public function getFechaRegistroFormateadaAttribute()
    {
        try {
            return Carbon::parse($this->fecha_registro)->format('d/m/Y');
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }

    public function scopePorCodigo($query, $codigo)
    {
        return $query->where('codigo_cliente', 'like', "%{$codigo}%");
    }

    // ✅ SCOPES PARA FILTRAR POR ESTADO
    public function scopeActivos($query)
    {
        return $query->where('estado_cuenta', self::ESTADO_ACTIVO);
    }

    public function scopeInactivos($query)
    {
        return $query->where('estado_cuenta', self::ESTADO_INACTIVO);
    }

    // ✅ MÉTODO PARA VERIFICAR SI ESTÁ ACTIVO
    public function estaActivo()
    {
        return $this->estado_cuenta === self::ESTADO_ACTIVO;
    }

    // ✅ MÉTODO PARA VERIFICAR SI PUEDE SER INACTIVADO
    public function puedeInactivar()
    {
        // No puede inactivarse si tiene propiedades activas
        return !$this->properties()
            ->whereIn('estado', ['activo', 'corte_pendiente'])
            ->exists();
    }

    // ✅ MÉTODO PARA OBTENER ESTADO LEGIBLE
    public function getEstadoLegibleAttribute()
    {
        return $this->estado_cuenta === self::ESTADO_ACTIVO ? 'Activo' : 'Inactivo';
    }

    // ✅ MÉTODO PARA OBTENER COLOR DEL BADGE
    public function getEstadoColorAttribute()
    {
        return $this->estado_cuenta === self::ESTADO_ACTIVO ? 'success' : 'warning';
    }
}