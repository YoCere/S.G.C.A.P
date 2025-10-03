<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fine extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'multas';

    // Tipos de multa predefinidos
    const TIPO_RECONEXION_3MESES = 'reconexion_3meses';
    const TIPO_RECONEXION_12MESES = 'reconexion_12meses';
    const TIPO_CONEXION_CLANDESTINA = 'conexion_clandestina';
    const TIPO_MANIPULACION_LLAVES = 'manipulacion_llaves';
    const TIPO_CONSTRUCCION = 'construccion';
    const TIPO_OTRO = 'otro';

    // Estados de la multa
    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_PAGADA = 'pagada';
    const ESTADO_ANULADA = 'anulada';

    protected $fillable = [
        'deuda_id',
        'propiedad_id',
        'tipo',
        'nombre',
        'monto',
        'descripcion',
        'fecha_aplicacion',
        'estado',
        'aplicada_automaticamente',
        'activa',
        'creado_por'
    ];

    protected $casts = [
        'fecha_aplicacion' => 'date',
        'aplicada_automaticamente' => 'boolean',
        'activa' => 'boolean',
        'monto' => 'decimal:2'
    ];

    // Relaciones
    public function deuda()
    {
        return $this->belongsTo(Debt::class, 'deuda_id');
    }

    public function propiedad()
    {
        return $this->belongsTo(Property::class, 'propiedad_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    // Scopes para el CRUD
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    public function scopePorTipo($query, $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    public function scopeDelMes($query, $mes = null)
    {
        $mes = $mes ?: now()->format('Y-m');
        return $query->whereYear('fecha_aplicacion', substr($mes, 0, 4))
                    ->whereMonth('fecha_aplicacion', substr($mes, 5, 2));
    }

    // Métodos de negocio
    public static function obtenerTiposMulta()
    {
        return [
            self::TIPO_RECONEXION_3MESES => 'Reconexión (3 meses de mora)',
            self::TIPO_RECONEXION_12MESES => 'Reconexión (12 meses de mora)',
            self::TIPO_CONEXION_CLANDESTINA => 'Conexión Clandestina',
            self::TIPO_MANIPULACION_LLAVES => 'Manipulación de Llaves',
            self::TIPO_CONSTRUCCION => 'Construcciones',
            self::TIPO_OTRO => 'Otro'
        ];
    }

    public static function obtenerMontosBase()
    {
        return [
            self::TIPO_RECONEXION_3MESES => 100.00,
            self::TIPO_RECONEXION_12MESES => 300.00,
            self::TIPO_CONEXION_CLANDESTINA => 500.00,
            self::TIPO_MANIPULACION_LLAVES => 500.00,
            self::TIPO_CONSTRUCCION => 200.00,
            self::TIPO_OTRO => 0.00
        ];
    }

    public function obtenerMontoBase()
    {
        return self::obtenerMontosBase()[$this->tipo] ?? 0;
    }

    public function archivar()
    {
        $this->update(['activa' => false]);
    }

    public function restaurar()
    {
        $this->update(['activa' => true]);
    }

    public function marcarComoPagada()
    {
        $this->update(['estado' => self::ESTADO_PAGADA]);
    }

    public function anular()
    {
        $this->update(['estado' => self::ESTADO_ANULADA]);
    }

    // Accesors para la vista
    public function getNombreTipoAttribute()
    {
        return self::obtenerTiposMulta()[$this->tipo] ?? 'Desconocido';
    }

    public function getColorEstadoAttribute()
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'warning',
            self::ESTADO_PAGADA => 'success',
            self::ESTADO_ANULADA => 'danger',
            default => 'secondary'
        };
    }

    public function getEsAutomaticaAttribute()
    {
        return $this->aplicada_automaticamente;
    }
}