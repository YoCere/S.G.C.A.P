@extends('adminlte::page')

@section('title', 'Crear Nueva Multa - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-plus-circle text-success"></i>
            Crear Nueva Multa
        </h1>
        <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Listado
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información de la Multa
                    </h3>
                </div>
                <form action="{{ route('admin.multas.store') }}" method="POST" id="multaForm">
                    @csrf
                    <div class="card-body">
                        <!-- Propiedad -->
                        <div class="form-group">
                            <label for="propiedad_id">Propiedad *</label>
                            <select name="propiedad_id" id="propiedad_id" class="form-control select2" required>
                                <option value="">Seleccione una propiedad</option>
                                @foreach($propiedades as $propiedad)
                                    <option value="{{ $propiedad->id }}" 
                                            {{ old('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                                        {{ $propiedad->referencia }} - {{ $propiedad->client->nombre }}
                                        ({{ $propiedad->barrio }})
                                    </option>
                                @endforeach
                            </select>
                            @error('propiedad_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="deuda_id">Deuda Asociada (Solo para multas por reconexión)</label>
                            <select name="deuda_id" id="deuda_id" class="form-control select2">
                                <option value="">Sin deuda asociada</option>
                                @foreach($deudas as $deuda)
                                    <option value="{{ $deuda->id }}" 
                                            {{ old('deuda_id') == $deuda->id ? 'selected' : '' }}>
                                        Deuda {{ $deuda->fecha_emision->format('d/m/Y') }} - 
                                        Bs. {{ number_format($deuda->monto_pendiente, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                Solo seleccione una deuda si la multa es por reconexión después de mora
                            </small>
                        </div>
                        
                        <!-- Tipo de Multa -->
                        <div class="form-group">
                            <label for="tipo">Tipo de Multa *</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="">Seleccione el tipo de multa</option>
                                @foreach($tipos as $key => $nombre)
                                    <option value="{{ $key }}" 
                                            data-monto="{{ $montosBase[$key] ?? 0 }}"
                                            {{ old('tipo') == $key ? 'selected' : '' }}>
                                        {{ $nombre }} 
                                        @if(isset($montosBase[$key]) && $montosBase[$key] > 0)
                                            - Bs. {{ number_format($montosBase[$key], 2) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre de la Multa *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" 
                                   value=" " placeholder="Ej: Multa por conexión clandestina" required>
                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Monto -->
                        <div class="form-group">
                            <label for="monto">Monto (Bs.) *</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Bs.</span>
                                </div>
                                <input type="number" name="monto" id="monto" class="form-control" 
                                       step="0.01" min="0" value="{{ old('monto', 0) }}" required>
                            </div>
                            <small class="form-text text-muted">
                                Monto base sugerido: <span id="montoBase">Bs. 0.00</span>
                            </small>
                            @error('monto')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Fecha de Aplicación -->
                        <div class="form-group">
                            <label for="fecha_aplicacion">Fecha de Aplicación *</label>
                            <input type="date" name="fecha_aplicacion" id="fecha_aplicacion" 
                                   class="form-control" readonly value="{{ old('fecha_aplicacion', date('Y-m-d')) }}" required>
                            @error('fecha_aplicacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción Detallada *</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" 
                                      rows="4" placeholder="Describa los detalles de la multa..." required>{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Multa
                        </button>
                        <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Ayuda -->
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle"></i>
                        Información Importante
                    </h3>
                </div>
                <div class="card-body">
                    <h5>Tipos de Multa:</h5>
                    <ul class="list-unstyled">
                        <li><strong>Reconexión (3 meses):</strong> Bs. 100.00</li>
                        <li><strong>Reconexión (12 meses):</strong> Bs. 300.00</li>
                        <li><strong>Conexión Clandestina:</strong> Bs. 500.00</li>
                        <li><strong>Manipulación Llaves:</strong> Bs. 500.00</li>
                        <li><strong>Construcciones:</strong> Bs. 200.00</li>
                    </ul>
                    <hr>
                    <h5>Notas:</h5>
                    <ul>
                        <li>Los campos marcados con * son obligatorios</li>
                        <li>Las multas se crean en estado "Pendiente"</li>
                        <li>Puede asociar la multa a una deuda específica o dejarla independiente</li>
                        <li>Al crear multas por conexión clandestina o manipulación de llaves, 
                            la propiedad se marcará automáticamente como "Cortada"</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container--default .select2-selection--single {
            height: calc(2.25rem + 2px);
            padding: 0.375rem 0.75rem;
        }
        .card-outline {
            border-top: 3px solid;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: "Seleccione una opción",
                allowClear: true
            });

            // Actualizar monto cuando cambie el tipo de multa
            $('#tipo').change(function() {
                const selectedOption = $(this).find('option:selected');
                const montoBase = selectedOption.data('monto') || 0;
                
                $('#monto').val(montoBase);
                $('#montoBase').text('Bs. ' + montoBase.toFixed(2));
                
                // Auto-completar nombre si está vacío
                if (!$('#nombre').val()) {
                    $('#nombre').val(selectedOption.text().split(' - ')[0]);
                }
            });

            // Auto-completar descripción si está vacía
            $('#tipo').change(function() {
                if (!$('#descripcion').val()) {
                    const tipoText = $(this).find('option:selected').text().split(' - ')[0];
                    $('#descripcion').val(`Multa aplicada por: ${tipoText}. `);
                }
            });

            // Validación del formulario
            $('#multaForm').on('submit', function(e) {
                const monto = parseFloat($('#monto').val());
                if (monto < 0) {
                    e.preventDefault();
                    alert('El monto no puede ser negativo');
                    return false;
                }
            });

            // Trigger change en carga para inicializar valores
            $('#tipo').trigger('change');
            $('#tipo').change(function() {
        const tipo = $(this).val();
        const esReconexion = tipo === 'reconexion_3meses' || tipo === 'reconexion_12meses';
        
        if (esReconexion) {
            $('#deuda_id').closest('.form-group').show();
            $('#deuda_id').prop('required', true);
        } else {
            $('#deuda_id').closest('.form-group').hide();
            $('#deuda_id').prop('required', false).val('');
        }
    });

    // Ocultar inicialmente
    $('#deuda_id').closest('.form-group').hide();
        });
    </script>
@stop