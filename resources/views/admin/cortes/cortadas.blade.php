@extends('adminlte::page')

@section('title', 'Cortes Realizados - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-ban text-danger"></i>
            Cortes Realizados
        </h1>
        <div>
            <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-clock"></i> Ver Cortes Pendientes
            </a>
        </div>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card card-outline card-danger">
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
            <form action="{{ route('admin.cortes.cortadas') }}" method="GET" class="row g-3">
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
                    <button type="submit" class="btn btn-danger btn-sm me-2">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.cortes.cortadas') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Cortes Realizados -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Propiedades con Corte Realizado</h3>
            <div class="card-tools">
                <span class="badge badge-danger">
                    {{ $propiedades->total() }} propiedades cortadas
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
                                <th>Deudas Cortadas</th>
                                <th>Multas Pendientes</th>
                                <th>Fecha Corte</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propiedades as $propiedad)
                                <tr>
                                    <td>
                                        <strong>{{ $propiedad->referencia }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $propiedad->client->nombre }}</strong>
                                        <br>
                                        <small class="text-muted">CI: {{ $propiedad->client->ci }}</small>
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
                                            $multasPendientes = $propiedad->multas->where('estado', \App\Models\Fine::ESTADO_PENDIENTE);
                                        @endphp
                                        @if($multasPendientes->count() > 0)
                                            <span class="badge badge-warning">
                                                {{ $multasPendientes->count() }} multa(s)
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                Total: Bs. {{ number_format($multasPendientes->sum('monto'), 2) }}
                                            </small>
                                        @else
                                            <span class="badge badge-success">Sin multas</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $propiedad->updated_at->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">{{ $propiedad->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">CORTADO</span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.properties.show', $propiedad) }}" 
                                               class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.pagos.create') }}?propiedad_id={{ $propiedad->id }}" 
                                               class="btn btn-success" title="Registrar pago">
                                                <i class="fas fa-money-bill"></i>
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
                    <h4 class="text-success">No hay cortes realizados</h4>
                    <p class="text-muted">Todas las propiedades están activas o con corte pendiente.</p>
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
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#barrio').change(function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@stop