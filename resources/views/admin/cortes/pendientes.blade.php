@extends('adminlte::page')

@section('title', 'Cortes Pendientes - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-clock text-warning"></i>
            Cortes Pendientes
        </h1>
        <div>
            <a href="{{ route('admin.cortes.cortadas') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-ban"></i> Ver Cortes Realizados
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                Filtros de Búsqueda
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.cortes.pendientes') }}" method="GET" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Búsqueda</label>
                    <input type="text" name="search" id="search" class="form-control form-control-sm" 
                           placeholder="Referencia, cliente, cédula..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <label for="barrio" class="form-label">Barrio</label>
                    <select name="barrio" id="barrio" class="form-control form-control-sm">
                        <option value="">Todos los barrios</option>
                        @foreach($barrios as $barrio)
                            <option value="{{ $barrio }}" {{ request('barrio') == $barrio ? 'selected' : '' }}>
                                {{ $barrio }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-warning btn-sm me-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Cortes Pendientes</span>
                    <span class="info-box-number">{{ $propiedades->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mora Crítica</span>
                    <span class="info-box-number">
                        {{ $propiedades->where('debts.0.fecha_vencimiento', '<', now()->subDays(120))->count() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box bg-gradient-info">
                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Diferentes Barrios</span>
                    <span class="info-box-number">{{ $barrios->count() }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Cortes Pendientes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Propiedades con Corte Pendiente</h3>
            <div class="card-tools">
                <span class="badge badge-warning">
                    {{ $propiedades->total() }} propiedades
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($propiedades->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Propiedad</th>
                                <th>Cliente</th>
                                <th>Barrio</th>
                                <th>Deudas Pendientes</th>
                                <th>Meses Mora</th>
                                <th>Última Deuda</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propiedades as $propiedad)
                                <tr>
                                    <td>
                                        <strong>{{ $propiedad->referencia }}</strong>
                                        <br>
                                        <small class="text-muted">Reg: {{ $propiedad->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $propiedad->client->nombre }}</strong>
                                        <br>
                                        <small class="text-muted">CI: {{ $propiedad->client->ci }}</small>
                                        <br>
                                        <small class="text-muted">Tel: {{ $propiedad->client->telefono }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-danger">
                                            Bs. {{ number_format($propiedad->debts->sum('monto_pendiente'), 2) }}
                                        </strong>
                                        <br>
                                        <small class="text-muted">{{ $propiedad->debts->count() }} deuda(s)</small>
                                    </td>
                                    <td>
                                        @php
                                            $deudaMasAntigua = $propiedad->debts->sortBy('fecha_emision')->first();
                                            $mesesMora = $deudaMasAntigua ? now()->diffInMonths($deudaMasAntigua->fecha_vencimiento) : 0;
                                        @endphp
                                        <span class="badge badge-{{ $mesesMora >= 6 ? 'danger' : 'warning' }}">
                                            {{ $mesesMora }} meses
                                        </span>
                                    </td>
                                    <td>
                                        @if($deudaMasAntigua)
                                            {{ $deudaMasAntigua->fecha_emision->format('m/Y') }}
                                            <br>
                                            <small class="text-muted">
                                                Vence: {{ $deudaMasAntigua->fecha_vencimiento->format('d/m/Y') }}
                                            </small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <form action="{{ route('admin.cortes.marcar-cortado', $propiedad) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-danger" 
                                                        title="Marcar como cortado físicamente"
                                                        onclick="return confirm('¿Marcar esta propiedad como CORTADA FÍSICAMENTE? Se aplicará multa automáticamente.')">
                                                    <i class="fas fa-ban"></i> Cortar
                                                </button>
                                            </form>
                                            <a href="{{ route('admin.properties.show', $propiedad) }}" 
                                               class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-success">¡Excelente!</h4>
                    <p class="text-muted">No hay propiedades con corte pendiente en este momento.</p>
                </div>
            @endif
        </div>
        <div class="card-footer clearfix">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Mostrando {{ $propiedades->firstItem() ?? 0 }} a {{ $propiedades->lastItem() ?? 0 }} 
                    de {{ $propiedades->total() }} registros
                </div>
                <div>
                    {{ $propiedades->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-responsive {
            min-height: 400px;
        }
        .info-box {
            cursor: default;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-submit form cuando cambien los selects de filtro
            $('#barrio').change(function() {
                $(this).closest('form').submit();
            });

            // Confirmación para corte físico
            $('form[action*="marcar-cortado"]').on('submit', function(e) {
                if (!confirm('¿CONFIRMAR CORTE FÍSICO? Esta acción aplicará multa automáticamente y no se puede deshacer fácilmente.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@stop