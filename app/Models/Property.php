<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pago;

class Property extends Model
{
    use HasFactory;

    // 🆕 CONSTANTES DE ESTADO
    const ESTADO_PENDIENTE_CONEXION = 'pendiente_conexion';
    const ESTADO_ACTIVO = 'activo';
    const ESTADO_INACTIVO = 'inactivo';
    const ESTADO_CORTE_PENDIENTE = 'corte_pendiente';
    const ESTADO_CORTADO = 'cortado';

    // 🆕 CONSTANTES PARA TIPOS DE TRABAJO PENDIENTE
    const TRABAJO_CONEXION_NUEVA = 'conexion_nueva';
    const TRABAJO_CORTE_MORA = 'corte_mora';
    const TRABAJO_RECONEXION = 'reconexion';

    // 🆕 MÉTODO PARA OBTENER TODOS LOS ESTADOS
    public static function getEstados()
    {
        return [
            self::ESTADO_PENDIENTE_CONEXION,
            self::ESTADO_ACTIVO,
            self::ESTADO_INACTIVO,
            self::ESTADO_CORTE_PENDIENTE,
            self::ESTADO_CORTADO,
        ];
    }

    protected $table = 'propiedades';

    protected $fillable = [
        'cliente_id',
        'tarifa_id',
        'referencia',
        'barrio',
        'latitud',
        'longitud',
        'estado',
        'tipo_trabajo_pendiente', // 🆕 NUEVO CAMPO
    ];

    // 🆕 ACTUALIZADO: Método SIMPLIFICADO para determinar tipo de trabajo
    public function getTipoTrabajoPendienteAttribute()
    {
        // 🎯 AHORA DIRECTAMENTE del campo, sin lógica compleja
        return $this->attributes['tipo_trabajo_pendiente'];
    }

    // 🆕 MÉTODO PARA VERIFICAR SI TIENE TRABAJO PENDIENTE
    public function getTieneTrabajoPendienteAttribute()
    {
        return !is_null($this->tipo_trabajo_pendiente);
    }

    // 🆕 MÉTODO PARA OBTENER TEXTO DESCRIPTIVO DEL TRABAJO
    public function getTextoTrabajoPendienteAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return 'Conexión Nueva';
            case self::TRABAJO_CORTE_MORA:
                return 'Corte por Mora';
            case self::TRABAJO_RECONEXION:
                return 'Reconexión';
            default:
                return 'Sin trabajo pendiente';
        }
    }

    // 🆕 MÉTODO PARA OBTENER TEXTO DE ACCIÓN DEL BOTÓN
    public function getTextoAccionTrabajoAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return 'Activar';
            case self::TRABAJO_CORTE_MORA:
                return 'Cortar';
            case self::TRABAJO_RECONEXION:
                return 'Reconectar';
            default:
                return 'Procesar';
        }
    }

    // 🆕 MÉTODO PARA OBTENER ICONO DEL BOTÓN
    public function getIconoTrabajoAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return 'fa-faucet';
            case self::TRABAJO_CORTE_MORA:
                return 'fa-bolt';
            case self::TRABAJO_RECONEXION:
                return 'fa-plug';
            default:
                return 'fa-tools';
        }
    }

    // 🆕 MÉTODO PARA OBTENER COLOR DEL BADGE
    public function getColorTrabajoAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return 'success';
            case self::TRABAJO_CORTE_MORA:
                return 'warning';
            case self::TRABAJO_RECONEXION:
                return 'info';
            default:
                return 'secondary';
        }
    }

    // 🆕 MÉTODO PARA OBTENER CLASE CSS DE LA FILA
    public function getClaseFilaTrabajoAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return 'table-success';
            case self::TRABAJO_CORTE_MORA:
                return 'table-warning';
            case self::TRABAJO_RECONEXION:
                return 'table-info';
            default:
                return '';
        }
    }

    // 🆕 MÉTODO PARA OBTENER MENSAJE DE CONFIRMACIÓN
    public function getMensajeConfirmacionAttribute()
    {
        switch ($this->tipo_trabajo_pendiente) {
            case self::TRABAJO_CONEXION_NUEVA:
                return '¿Confirmar que completó la instalación y dejó el servicio funcionando?';
            case self::TRABAJO_CORTE_MORA:
                return '¿Confirmar corte físico? Se aplicará multa automáticamente.';
            case self::TRABAJO_RECONEXION:
                return '¿Confirmar reconexión física? El servicio se activará inmediatamente.';
            default:
                return '¿Confirmar que completó el trabajo?';
        }
    }

    // 🆕 MÉTODO PARA LIMPIAR TRABAJO PENDIENTE
    public function limpiarTrabajoPendiente()
    {
        $this->update(['tipo_trabajo_pendiente' => null]);
    }

    // 🆕 MÉTODO PARA ASIGNAR TRABAJO PENDIENTE
    public function asignarTrabajoPendiente($tipoTrabajo)
    {
        $this->update([
            'estado' => self::ESTADO_CORTE_PENDIENTE, // ✅ DEBE cambiar a CORTE_PENDIENTE
            'tipo_trabajo_pendiente' => $tipoTrabajo
        ]);
        
        \Log::info("✅ Trabajo pendiente asignado - Propiedad: {$this->id}, Estado: CORTE_PENDIENTE, Trabajo: {$tipoTrabajo}");
    }

    public function obtenerMesesAdeudados()
    {
        try {
            \Log::info("🔍 Calculando meses adeudados para propiedad: {$this->id}");

            // Obtener TODOS los meses pagados para esta propiedad
            $mesesPagados = Pago::where('propiedad_id', $this->id)
                ->pluck('mes_pagado')
                ->toArray();

            \Log::info("💰 Meses pagados: " . json_encode($mesesPagados));

            // Obtener meses con deudas PENDIENTES
            $mesesConDeudaPendiente = Debt::where('propiedad_id', $this->id)
                ->where('estado', 'pendiente')
                ->where('monto_pendiente', '>', 0)
                ->get()
                ->map(function($deuda) {
                    return $deuda->fecha_emision->format('Y-m');
                })
                ->toArray();

            \Log::info("📋 Meses con deuda pendiente: " . json_encode($mesesConDeudaPendiente));

            // ✅ CORRECCIÓN: Solo son meses adeudados los que tienen deuda pendiente
            // Y que no están pagados (por si hay inconsistencia)
            $mesesAdeudados = array_filter($mesesConDeudaPendiente, function($mes) use ($mesesPagados) {
                return !in_array($mes, $mesesPagados);
            });

            \Log::info("✅ Meses adeudados finales: " . json_encode($mesesAdeudados) . " - Total: " . count($mesesAdeudados));

            return array_values($mesesAdeudados); // Reindexar array

        } catch (\Exception $e) {
            \Log::error("💥 Error en obtenerMesesAdeudados para propiedad {$this->id}: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalDeudasPendientesAttribute()
    {
        return $this->debts()->where('estado', 'pendiente')->sum('monto_pendiente');
    }

    // Relaciones
    public function client()
    {
        return $this->belongsTo(Client::class, 'cliente_id');
    }

    public function tariff()
    {
        return $this->belongsTo(\App\Models\Tariff::class, 'tarifa_id')->withTrashed();
    }

    public function debts()
    {
        return $this->hasMany(Debt::class, 'propiedad_id');
    }

    // Scopes útiles
    public function scopeActivas($q)
    {
        return $q->where('estado', self::ESTADO_ACTIVO);
    }

    // 🆕 SCOPE PARA PROPIEDADES PENDIENTES DE CONEXIÓN
    public function scopePendientesConexion($q)
    {
        return $q->where('estado', self::ESTADO_PENDIENTE_CONEXION);
    }

    public function getClienteNombreAttribute()
    {
        return $this->client ? $this->client->nombre : 'Cliente No Asignado';
    }

    public function multas()
    {
        return $this->hasMany(Fine::class, 'propiedad_id');
    }

    public function multasPendientes()
    {
        return $this->multas()->where('estado', Fine::ESTADO_PENDIENTE);
    }

    // En app/Models/Property.php - AGREGAR método:

/**
 * 🆕 MÉTODO: Forzar actualización a corte pendiente para reconexión
 */
    public function forzarReconexionPendiente()
    {
        $this->update([
            'estado' => self::ESTADO_CORTE_PENDIENTE,
            'tipo_trabajo_pendiente' => self::TRABAJO_RECONEXION
        ]);
        
        \Log::info("🔄 RECONEXIÓN FORZADA - Propiedad: {$this->id} ahora en CORTE_PENDIENTE");
        
        return $this->refresh();
    }
    public function obtenerPrimerMesAdeudado()
    {
        $mesesAdeudados = $this->obtenerMesesAdeudados();
        
        if (empty($mesesAdeudados)) {
            return null;
        }
        
        sort($mesesAdeudados);
        return $mesesAdeudados[0];
    }

    /**
     * 🆕 MÉTODO: Obtener el último mes adeudado
     */
    public function obtenerUltimoMesAdeudado()
    {
        $mesesAdeudados = $this->obtenerMesesAdeudados();
        
        if (empty($mesesAdeudados)) {
            return null;
        }
        
        sort($mesesAdeudados);
        return end($mesesAdeudados);
    }
    public function obtenerProximosMesesAPagar($cantidad = 3)
    {
        $mesesAdeudados = $this->obtenerMesesAdeudados();
        sort($mesesAdeudados);
        
        return array_slice($mesesAdeudados, 0, $cantidad);
    }
}