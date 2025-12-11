<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigMultaMora extends Model
{
    use HasFactory;

    protected $table = 'config_multa_moras';

    protected $fillable = [
        'nombre',
        'descripcion',
        'meses_gracia',
        'porcentaje_multa',
        'activo',
    ];

    protected $casts = [
        'meses_gracia' => 'integer',
        'porcentaje_multa' => 'decimal:2',
        'activo' => 'boolean',
    ];

    // Obtener la configuración activa
    public static function getConfiguracionActiva(): ?self
    {
        return static::where('activo', true)->first();
    }

    // Verificar si está activa la configuración
    public static function estaActiva(): bool
    {
        return (bool) static::getConfiguracionActiva();
    }

    // Validar si se debe aplicar multa
    public function debeAplicarMulta(int $mesesAtrasados): bool
    {
        return $this->activo && $mesesAtrasados >= $this->meses_gracia;
    }

    // Calcular monto de multa
    public function calcularMulta(float $montoBase, int $mesesAtrasados): float
    {
        if (!$this->debeAplicarMulta($mesesAtrasados)) {
            return 0.00;
        }
        
        return $montoBase * ($this->porcentaje_multa / 100);
    }

    // Obtener descripción detallada
    public function getDescripcionCompletaAttribute(): string
    {
        return sprintf(
            "%s\nMeses de gracia: %d\nPorcentaje de multa: %.2f%%\nEstado: %s",
            $this->nombre,
            $this->meses_gracia,
            $this->porcentaje_multa,
            $this->activo ? 'Activo' : 'Inactivo'
        );
    }

    // Activar configuración
    public function activar(): bool
    {
        // Desactivar todas las demás configuraciones
        static::query()->update(['activo' => false]);
        
        return $this->update(['activo' => true]);
    }

    // Desactivar configuración
    public function desactivar(): bool
    {
        return $this->update(['activo' => false]);
    }

    // Crear configuración predeterminada si no existe
    public static function crearConfiguracionPredeterminada(): self
    {
        return static::firstOrCreate(
            ['nombre' => 'Multa por mora estándar'],
            [
                'descripcion' => 'Se aplica después de 3 meses de atraso, 10% sobre el monto base',
                'meses_gracia' => 3,
                'porcentaje_multa' => 10.00,
                'activo' => true,
            ]
        );
    }

    // Obtener configuración actual o crear predeterminada
    public static function obtenerConfiguracion(): self
    {
        $config = static::getConfiguracionActiva();
        
        if (!$config) {
            $config = static::crearConfiguracionPredeterminada();
        }
        
        return $config;
    }
}