@extends('layouts.admin-ultralight')

@section('title', 'Editar Multa - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-edit text-warning"></i>
            Editar Multa #{{ $multa->id }}
        </h1>
        <div>
            <a href="{{ route('admin.multas.show', $multa) }}" class="btn btn-info btn-sm">
                <i class="fas fa-eye"></i> Ver Detalles
            </a>
            <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    @if($multa->aplicada_automaticamente || $multa->estado === \App\Models\Fine::ESTADO_PAGADA)
        <div class="alert alert-warning">
            <h5><i class="icon fas fa-exclamation-triangle"></i> Advertencia</h5>
            <p>
                @if($multa->aplicada_automaticamente)
                    Esta multa fue aplicada automáticamente por el sistema. 
                    Algunos campos pueden estar bloqueados para edición.
                @endif
                @if($multa->estado === \App\Models\Fine::ESTADO_PAGADA)
                    Esta multa ya ha sido marcada como pagada. 
                    No se recomienda modificar una multa ya pagada.
                @endif
            </p>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-edit"></i>
                        Editar Información de la Multa
                    </h3>
                </div>
                <form action="{{ route('admin.multas.update', $multa) }}" method="POST" id="multaForm">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <!-- Información de solo lectura -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Propiedad</label>
                                    <input type="text" class="form-control" 
                                           value="{{ $multa->propiedad->referencia ?? 'N/A' }} - {{ $multa->propiedad->cliente->nombre ?? 'N/A' }}" 
                                           readonly>
                                    <small class="form-text text-muted">
                                        La propiedad no puede ser modificada
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Estado Actual</label>
                                    <input type="text" class="form-control" 
                                           value="{{ ucfirst($multa->estado) }}" 
                                           readonly>
                                    <small class="form-text text-muted">
                                        El estado se actualiza mediante acciones específicas
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Campos editables -->
                        <!-- Tipo de Multa -->
                        <div class="form-group">
                            <label for="tipo">Tipo de Multa *</label>
                            <select name="tipo" id="tipo" class="form-control" required
                                    {{ $multa->aplicada_automaticamente ? 'disabled' : '' }}>
                                @foreach($tipos as $key => $nombre)
                                    <option value="{{ $key }}" 
                                            data-monto="{{ $montosBase[$key] ?? 0 }}"
                                            {{ old('tipo', $multa->tipo) == $key ? 'selected' : '' }}>
                                        {{ $nombre }} 
                                        @if(isset($montosBase[$key]) && $montosBase[$key] > 0)
                                            - Bs. {{ number_format($montosBase[$key], 2) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($multa->aplicada_automaticamente)
                                <input type="hidden" name="tipo" value="{{ $multa->tipo }}">
                            @endif
                            @error('tipo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Nombre -->
                        <div class="form-group">
                            <label for="nombre">Nombre de la Multa *</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" 
                                   value="{{ old('nombre', $multa->nombre) }}" 
                                   placeholder="Ej: Multa por conexión clandestina" required>
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
                                       step="0.01" min="0" 
                                       value="{{ old('monto', $multa->monto) }}" required>
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
                                   class="form-control" 
                                   value="{{ old('fecha_aplicacion', $multa->fecha_aplicacion->format('Y-m-d')) }}" 
                                   required>
                            @error('fecha_aplicacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label for="descripcion">Descripción Detallada *</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" 
                                      rows="4" placeholder="Describa los detalles de la multa..." 
                                      required>{{ old('descripcion', $multa->descripcion) }}</textarea>
                            @error('descripcion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save"></i> Actualizar Multa
                        </button>
                        <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                        
                        @if($multa->estado == 'pendiente')
                            <div class="float-right">
                                <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" 
                                            onclick="return confirm('¿Marcar esta multa como pagada?')">
                                        <i class="fas fa-check"></i> Marcar como Pagada
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel de Información -->
        <div class="col-md-4">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información de la Multa
                    </h3>
                </div>
                <div class="card-body">
                    <h5>Detalles Actuales:</h5>
                    <ul class="list-unstyled">
                        <li><strong>ID:</strong> #{{ $multa->id }}</li>
                        <li><strong>Creada por:</strong> {{ $multa->usuario->name ?? 'N/A' }}</li>
                        <li><strong>Fecha creación:</strong> {{ $multa->created_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Última actualización:</strong> {{ $multa->updated_at->format('d/m/Y H:i') }}</li>
                        <li><strong>Tipo:</strong> {{ $multa->nombre_tipo }}</li>
                        <li><strong>Estado:</strong> 
                            <span class="badge badge-{{ $multa->color_estado }}">
                                {{ ucfirst($multa->estado) }}
                            </span>
                        </li>
                        @if($multa->aplicada_automaticamente)
                            <li><strong class="text-info">Aplicada automáticamente</strong></li>
                        @endif
                    </ul>
                    <hr>
                    
                    <h5>Acciones Disponibles:</h5>
                    <div class="d-grid gap-2">
                        @if($multa->estado == 'pendiente')
                            <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm btn-block" 
                                        onclick="return confirm('¿Marcar esta multa como pagada?')">
                                    <i class="fas fa-check"></i> Marcar como Pagada
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.multas.anular', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm btn-block" 
                                        onclick="return confirm('¿ANULAR esta multa? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-ban"></i> Anular Multa
                                </button>
                            </form>
                        @endif
                        
                        @if($multa->activa)
                            <form action="{{ route('admin.multas.destroy', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-sm btn-block" 
                                        onclick="return confirm('¿Archivar esta multa?')">
                                    <i class="fas fa-archive"></i> Archivar Multa
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.multas.restaurar', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm btn-block" 
                                        onclick="return confirm('¿Restaurar esta multa?')">
                                    <i class="fas fa-undo"></i> Restaurar Multa
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-outline {
            border-top: 3px solid;
        }
        .btn-block {
            margin-bottom: 5px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Actualizar monto cuando cambie el tipo de multa
            $('#tipo').change(function() {
                const selectedOption = $(this).find('option:selected');
                const montoBase = selectedOption.data('monto') || 0;
                
                $('#montoBase').text('Bs. ' + montoBase.toFixed(2));
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
        });
    </script>
@stop