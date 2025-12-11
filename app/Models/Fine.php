<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    const TIPO_MORA_PAGO = 'mora_pago';
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
        'porcentaje_aplicado', // ✅ NUEVO
        'meses_atraso',        // ✅ NUEVO
        'mes_aplicado',        // ✅ NUEVO
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
        'monto' => 'decimal:2',
        'porcentaje_aplicado' => 'decimal:2', // ✅ NUEVO
        'meses_atraso' => 'integer',          // ✅ NUEVO
    ];

    // Relaciones
    public function deuda(): BelongsTo
    {
        return $this->belongsTo(Debt::class, 'deuda_id');
    }

    public function propiedad(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'propiedad_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function pagos(): BelongsToMany
    {
        return $this->belongsToMany(Pago::class, 'multa_pago', 'multa_id', 'pago_id')
                    ->withPivot('monto_pagado')
                    ->withTimestamps();
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

    // 🆕 SCOPE: Multas por mora en pagos
    public function scopePorMora($query)
    {
        return $query->where('tipo', self::TIPO_MORA_PAGO);
    }

    // 🆕 SCOPE: Multas aplicadas automáticamente
    public function scopeAutomaticas($query)
    {
        return $query->where('aplicada_automaticamente', true);
    }

    // Métodos de negocio
    public static function obtenerTiposMulta(): array
    {
        return [
            self::TIPO_RECONEXION_3MESES => 'Reconexión (3 meses de mora)',
            self::TIPO_RECONEXION_12MESES => 'Reconexión (12 meses de mora)',
            self::TIPO_CONEXION_CLANDESTINA => 'Conexión Clandestina',
            self::TIPO_MANIPULACION_LLAVES => 'Manipulación de Llaves',
            self::TIPO_CONSTRUCCION => 'Construcciones',
            self::TIPO_MORA_PAGO => 'Mora en pago',
            self::TIPO_OTRO => 'Otro'
        ];
    }

    public static function obtenerMontosBase(): array
    {
        return [
            self::TIPO_RECONEXION_3MESES => 100.00,
            self::TIPO_RECONEXION_12MESES => 300.00,
            self::TIPO_CONEXION_CLANDESTINA => 500.00,
            self::TIPO_MANIPULACION_LLAVES => 500.00,
            self::TIPO_CONSTRUCCION => 200.00,
            self::TIPO_MORA_PAGO => 0.00, // 🆕 Se calcula dinámicamente
            self::TIPO_OTRO => 0.00
        ];
    }

    public function obtenerMontoBase(): float
    {
        return self::obtenerMontosBase()[$this->tipo] ?? 0;
    }

    public function archivar(): bool
    {
        return $this->update(['activa' => false]);
    }

    public function restaurar(): bool
    {
        return $this->update(['activa' => true]);
    }

    public function marcarComoPagada(): bool
    {
        return $this->update(['estado' => self::ESTADO_PAGADA]);
    }

    public function anular(): bool
    {
        return $this->update(['estado' => self::ESTADO_ANULADA]);
    }

    // Accesors para la vista
    public function getNombreTipoAttribute(): string
    {
        return self::obtenerTiposMulta()[$this->tipo] ?? 'Desconocido';
    }

    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            self::ESTADO_PENDIENTE => 'warning',
            self::ESTADO_PAGADA => 'success',
            self::ESTADO_ANULADA => 'danger',
            default => 'secondary'
        };
    }

    public function getEsAutomaticaAttribute(): bool
    {
        return $this->aplicada_automaticamente;
    }

    // 🆕 SCOPE: Multas de reconexión pendientes por propiedad
    public function scopePendingReconnectionFor($query, $propertyId)
    {
        return $query->where('propiedad_id', $propertyId)
                    ->where('estado', self::ESTADO_PENDIENTE)
                    ->where('activa', true)
                    ->whereIn('tipo', [self::TIPO_RECONEXION_3MESES, self::TIPO_RECONEXION_12MESES]);
    }

    // 🆕 MÉTODO: Verificar si ya existe multa de reconexión pendiente
    public static function existeMultaReconexionPendiente($propertyId): bool
    {
        return self::pendingReconnectionFor($propertyId)->exists();
    }

    // 🆕 MÉTODO: Obtener multa de reconexión pendiente existente
    public static function obtenerMultaReconexionPendiente($propertyId)
    {
        return self::pendingReconnectionFor($propertyId)->first();
    }

    // 🆕 NUEVOS MÉTODOS PARA MORA EN PAGO

    /**
     * Crea una multa automática por mora en pago
     */
    public static function crearMultaPorMora(
        int $propiedadId,
        float $montoBase,
        int $mesesAtrasados,
        string $mesPagado,
        int $creadoPor,
        ?int $pagoId = null
    ): ?Fine {
        $config = ConfigMultaMora::getConfiguracionActiva();
        
        if (!$config || !$config->activo || $mesesAtrasados < $config->meses_gracia) {
            return null;
        }

        $multaMonto = $montoBase * ($config->porcentaje_multa / 100);
        
        $multa = self::create([
            'propiedad_id' => $propiedadId,
            'tipo' => self::TIPO_MORA_PAGO,
            'nombre' => "Multa por mora - {$mesesAtrasados} mes(es) atrasado(s)",
            'descripcion' => sprintf(
                "Pago del mes %s registrado con %d mes(es) de atraso. " .
                "Se aplicó %.2f%% de multa según configuración '%s'.",
                $mesPagado,
                $mesesAtrasados,
                $config->porcentaje_multa,
                $config->nombre
            ),
            'monto' => $multaMonto,
            'fecha_aplicacion' => now(),
            'estado' => self::ESTADO_PENDIENTE,
            'aplicada_automaticamente' => true,
            'activa' => true,
            'creado_por' => $creadoPor,
        ]);

        // Si hay pago asociado, relacionarlos
        if ($pagoId) {
            $multa->pagos()->attach($pagoId, ['monto_pagado' => 0]);
        }

        return $multa;
    }

    /**
     * Calcula la multa por mora basada en configuración
     */
    public static function calcularMultaPorMora(
        float $montoBase,
        int $mesesAtrasados
    ): array {
        $config = ConfigMultaMora::getConfiguracionActiva();
        
        $aplicaMulta = false;
        $montoMulta = 0.00;
        $totalAPagar = $montoBase;
        
        if ($config && $config->activo && $mesesAtrasados >= $config->meses_gracia) {
            $aplicaMulta = true;
            $montoMulta = $montoBase * ($config->porcentaje_multa / 100);
            $totalAPagar = $montoBase + $montoMulta;
        }

        return [
            'aplica' => $aplicaMulta,
            'meses_gracia' => $config->meses_gracia ?? 3,
            'porcentaje_multa' => $config->porcentaje_multa ?? 10.00,
            'meses_atrasados' => $mesesAtrasados,
            'monto_base' => $montoBase,
            'monto_multa' => $montoMulta,
            'total_a_pagar' => $totalAPagar,
            'configuracion' => $config,
        ];
    }

    /**
     * Obtiene todas las multas por mora pendientes de una propiedad
     */
    public static function obtenerMultasMoraPendientes(int $propiedadId)
    {
        return self::where('propiedad_id', $propiedadId)
                    ->where('tipo', self::TIPO_MORA_PAGO)
                    ->where('estado', self::ESTADO_PENDIENTE)
                    ->where('activa', true)
                    ->get();
    }

    /**
     * Verifica si una propiedad tiene multas por mora pendientes
     */
    public static function tieneMultasMoraPendientes(int $propiedadId): bool
    {
        return self::where('propiedad_id', $propiedadId)
                    ->where('tipo', self::TIPO_MORA_PAGO)
                    ->where('estado', self::ESTADO_PENDIENTE)
                    ->where('activa', true)
                    ->exists();
    }

    /**
     * Total de multas por mora pendientes de una propiedad
     */
    public static function totalMultasMoraPendientes(int $propiedadId): float
    {
        return self::where('propiedad_id', $propiedadId)
                    ->where('tipo', self::TIPO_MORA_PAGO)
                    ->where('estado', self::ESTADO_PENDIENTE)
                    ->where('activa', true)
                    ->sum('monto');
    }

    /**
     * Marca multas por mora como pagadas cuando se registra un pago
     */
    public static function marcarMultasMoraComoPagadas(
        int $propiedadId,
        int $pagoId,
        float $montoPagado
    ): array {
        $multas = self::obtenerMultasMoraPendientes($propiedadId);
        $multasPagadas = [];
        
        foreach ($multas as $multa) {
            if ($montoPagado >= $multa->monto) {
                $multa->estado = self::ESTADO_PAGADA;
                $multa->save();
                
                // Actualizar relación con pago
                $multa->pagos()->syncWithoutDetaching([
                    $pagoId => ['monto_pagado' => $multa->monto]
                ]);
                
                $multasPagadas[] = $multa;
                $montoPagado -= $multa->monto;
            }
        }
        
        return $multasPagadas;
    }

    /**
     * Verifica si la multa es de tipo mora
     */
    public function esMoraPago(): bool
    {
        return $this->tipo === self::TIPO_MORA_PAGO;
    }

    /**
     * Obtiene detalles de la configuración de mora aplicada
     */
    public function obtenerDetallesMora(): ?string
    {
        if (!$this->esMoraPago()) {
            return null;
        }
        
        // Buscar información de configuración en la descripción
        if (preg_match('/(\d+(?:\.\d+)?)%/', $this->descripcion, $matches)) {
            $porcentaje = $matches[1];
            return "Multa aplicada: {$porcentaje}% sobre el monto base";
        }
        
        return "Multa por mora aplicada";
    }

    /**
     * Formatea el monto para mostrar en vistas
     */
    public function getMontoFormateadoAttribute(): string
    {
        return 'Bs ' . number_format($this->monto, 2);
    }

    /**
     * Formatea la fecha de aplicación
     */
    public function getFechaAplicacionFormateadaAttribute(): string
    {
        return $this->fecha_aplicacion->format('d/m/Y');
    }

    /**
     * Retorna los días transcurridos desde la aplicación
     */
    public function getDiasTranscurridosAttribute(): int
    {
        return $this->fecha_aplicacion->diffInDays(now());
    }
}