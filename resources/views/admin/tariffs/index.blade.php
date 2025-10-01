@extends('adminlte::page')

@section('title', 'Tarifas')

@section('content_header')
    <h1>Gestión de Tarifas</h1>
    <small class="text-muted">Administra las tarifas disponibles para las propiedades</small>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong><i class="fas fa-check-circle mr-1"></i>{{ session('info') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.tariffs.create') }}">
                        <i class="fas fa-plus-circle mr-1"></i>Nueva Tarifa
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="form-inline float-right">
                        <label class="mr-2 mb-0 text-sm text-muted">Filtrar:</label>
                        <select class="form-control form-control-sm" onchange="window.location.href = this.value">
                            <option value="{{ route('admin.tariffs.index') }}?estado=activas" 
                                {{ request('estado') == 'activas' ? 'selected' : '' }}>
                                🟢 Tarifas Activas
                            </option>
                            <option value="{{ route('admin.tariffs.index') }}?estado=inactivas" 
                                {{ request('estado') == 'inactivas' ? 'selected' : '' }}>
                                🔴 Tarifas Inactivas
                            </option>
                            <option value="{{ route('admin.tariffs.index') }}?estado=todas" 
                                {{ request('estado') == 'todas' ? 'selected' : '' }}>
                                📋 Todas las Tarifas
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($tariffs->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">ID</th>
                                <th>Nombre</th>
                                <th width="120">Precio Mensual</th>
                                <th width="120">Estado</th>
                                <th width="100" class="text-center">Propiedades</th>
                                <th width="200" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tariffs as $tariff)
                                <tr>
                                    <td class="text-muted">#{{ $tariff->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <strong>{{ $tariff->nombre }}</strong>
                                            @if(!$tariff->activo)
                                                <span class="badge badge-warning ml-2">INACTIVA</span>
                                            @endif
                                        </div>
                                        @if($tariff->descripcion)
                                            <small class="text-muted d-block">{{ Str::limit($tariff->descripcion, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="text-success">Bs {{ number_format($tariff->precio_mensual, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($tariff->activo)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check-circle mr-1"></i>Activa
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-pause-circle mr-1"></i>Inactiva
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info badge-pill" 
                                              title="{{ $tariff->properties_count }} propiedades usan esta tarifa">
                                            {{ $tariff->properties_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm w-100" role="group">
                                            <a class="btn btn-outline-primary" 
                                               href="{{ route('admin.tariffs.edit', $tariff) }}"
                                               title="Editar tarifa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if($tariff->activo)
                                                <button class="btn btn-outline-warning" 
                                                        onclick="confirmDeactivate({{ $tariff->id }}, '{{ $tariff->nombre }}', {{ $tariff->properties_count ?? 0 }})"
                                                        title="Desactivar tarifa">
                                                    <i class="fas fa-pause"></i>
                                                </button>
                                            @else
                                                <form action="{{ route('admin.tariffs.activate', $tariff) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf @method('PUT')
                                                    <button class="btn btn-outline-success" type="submit"
                                                            title="Activar tarifa">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request('estado') == 'inactivas')
                            No hay tarifas inactivas
                        @elseif(request('estado') == 'activas')
                            No hay tarifas activas
                        @else
                            No hay tarifas registradas
                        @endif
                    </h4>
                    @if(!in_array(request('estado'), ['inactivas', 'activas']))
                        <a href="{{ route('admin.tariffs.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>Crear Primera Tarifa
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($tariffs->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $tariffs->count() }} de {{ $tariffs->total() }} tarifas
                    </div>
                    @if($tariffs->hasPages())
                        {{ $tariffs->links() }}
                    @endif
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .badge-pill {
            padding: 0.4em 0.6em;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeactivate(id, nombre, propertiesCount) {
    let propertiesText = '';
    if (propertiesCount > 0) {
        propertiesText = `<br><br>
            <div class="alert alert-warning text-left small mb-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Importante:</strong> Esta tarifa está siendo usada por <strong>${propertiesCount}</strong> propiedad(es).
                <ul class="mb-0 mt-1 pl-3">
                    <li>Las propiedades existentes seguirán funcionando normalmente</li>
                    <li>No podrá asignarse a nuevas propiedades</li>
                    <li>El historial de pagos se mantendrá intacto</li>
                </ul>
            </div>`;
    }

    Swal.fire({
        title: '¿Desactivar Tarifa?',
        html: `¿Está seguro de desactivar la tarifa: <strong>"${nombre}"</strong>?${propertiesText}`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="fas fa-pause mr-1"></i>Sí, desactivar',
        cancelButtonText: '<i class="fas fa-times mr-1"></i>Cancelar',
        reverseButtons: true,
        width: '600px'
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar formulario de desactivación
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/tariffs/${id}/deactivate`;
            form.innerHTML = `
                @csrf
                @method('PUT')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

// Auto-ocultar alertas después de 5 segundos
$(document).ready(function() {
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
});
</script>
@stop