@extends('layouts.admin-ultralight')

@section('title', 'Configurar Multa por Mora')

@section('content_header')
    <h1 class="h5 font-weight-bold mb-0">
        <i class="fas fa-cog text-info mr-2"></i>
        Configuración de Multa por Mora
    </h1>
    <small class="text-muted">Defina los parámetros para aplicar multas a pagos atrasados</small>
@stop

@section('content')
    @php
        $config = \App\Models\ConfigMultaMora::first();
        if (!$config) {
            $config = \App\Models\ConfigMultaMora::create([
                'nombre' => 'Multa por mora estándar',
                'descripcion' => 'Configuración automática generada',
                'meses_gracia' => 3,
                'porcentaje_multa' => 10.00,
                'activo' => true,
            ]);
        }
    @endphp

    <div class="row">
        <div class="col-md-8">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Parámetros de Configuración</h3>
                </div>
                <form action="{{ route('admin.config-multas-mora.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="form-group">
                            <label for="nombre">Nombre de la configuración</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="{{ old('nombre', $config->nombre) }}" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" 
                                      rows="2">{{ old('descripcion', $config->descripcion) }}</textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="meses_gracia">Meses de gracia</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="meses_gracia" 
                                               name="meses_gracia" min="1" max="12" step="1"
                                               value="{{ old('meses_gracia', $config->meses_gracia) }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">meses</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Meses que pueden pasar sin multa después del vencimiento
                                    </small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="porcentaje_multa">Porcentaje de multa</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="porcentaje_multa" 
                                               name="porcentaje_multa" min="0" max="100" step="0.01"
                                               value="{{ old('porcentaje_multa', $config->porcentaje_multa) }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        Porcentaje que se aplica sobre el monto base del pago
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="activo" 
                                       name="activo" value="1" 
                                       {{ old('activo', $config->activo) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="activo">
                                    Configuración activa
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Si está inactiva, no se generarán multas automáticas
                            </small>
                        </div>
                        
                        <!-- Ejemplo de cálculo -->
                        <div class="alert alert-light mt-4">
                            <h6 class="alert-heading">
                                <i class="fas fa-calculator mr-1"></i>
                                Ejemplo de cálculo
                            </h6>
                            <p class="mb-1">
                                Con la configuración actual, un pago de <strong>Bs 100</strong> 
                                con <strong>{{ $config->meses_gracia + 1 }} meses</strong> de atraso:
                            </p>
                            <div class="d-flex justify-content-between">
                                <span>Multa aplicable:</span>
                                <strong class="text-danger">
                                    Bs {{ number_format(100 * ($config->porcentaje_multa / 100), 2) }}
                                </strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Total a pagar:</span>
                                <strong class="text-success">
                                    Bs {{ number_format(100 + (100 * ($config->porcentaje_multa / 100)), 2) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-save mr-1"></i>
                            Guardar Configuración
                        </button>
                        <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>
                            Volver a Multas
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle mr-1"></i>
                        Información
                    </h3>
                </div>
                <div class="card-body">
                    <h6>¿Cómo funcionan las multas por mora?</h6>
                    <ul class="small pl-3">
                        <li>Se aplican automáticamente al registrar pagos atrasados</li>
                        <li>El sistema compara la fecha de pago con el mes que se está pagando</li>
                        <li>Solo se aplican si el atraso es igual o mayor al "meses de gracia"</li>
                        <li>El porcentaje se calcula sobre el monto base de cada pago atrasado</li>
                        <li>Las multas generadas aparecen en la lista de multas pendientes</li>
                    </ul>
                    
                    <hr>
                    
                    <h6>Ejemplo práctico:</h6>
                    <div class="small">
                        <p><strong>Configuración:</strong></p>
                        <ul>
                            <li>Meses de gracia: <strong>3</strong></li>
                            <li>Porcentaje de multa: <strong>10%</strong></li>
                        </ul>
                        <p><strong>Escenario:</strong></p>
                        <p>Cliente paga el mes de <strong>Enero</strong> en <strong>Abril</strong>:</p>
                        <ul>
                            <li>Atraso: 3 meses (Feb, Mar, Abr)</li>
                            <li>Como 3 ≥ 3 (meses gracia) → <span class="text-success">APLICA MULTA</span></li>
                            <li>Si el pago es Bs 100 → Multa: Bs 10</li>
                            <li><strong>Total a pagar: Bs 110</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    // Validación en tiempo real
    document.getElementById('meses_gracia').addEventListener('change', function() {
        if (this.value < 1) this.value = 1;
        if (this.value > 12) this.value = 12;
    });
    
    document.getElementById('porcentaje_multa').addEventListener('change', function() {
        if (this.value < 0) this.value = 0;
        if (this.value > 100) this.value = 100;
    });
</script>
@stop