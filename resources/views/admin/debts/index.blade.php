@extends('adminlte::page')

@section('title', 'Deudas')

@section('content_header')
    <h1>Lista de deudas</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        {{-- ✅ NUEVO: CARD HEADER CON BUSCADOR --}}
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-primary" href="{{ route('admin.debts.create') }}">Registrar deuda</a>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('admin.debts.index') }}" method="GET" class="form-inline float-right">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Buscar por cliente o propiedad..." 
                                   value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.debts.index') }}" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- ✅ MOSTRAR TÉRMINO DE BÚSQUEDA --}}
            @if(request('search'))
                <div class="alert alert-info mb-3">
                    Mostrando resultados para: <strong>"{{ request('search') }}"</strong>
                    <a href="{{ route('admin.debts.index') }}" class="float-right">Ver todos</a>
                </div>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Propiedad</th>
                        <th>Monto (Bs)</th>
                        <th>Fecha emisión</th>
                        <th>Estado Deuda</th>
                        <th>Estado Servicio</th>
                        <th colspan="3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($debts as $debt)
                        <tr>
                            <td>{{ $debt->id }}</td>
                            <td>{{ $debt->propiedad->client->nombre }}</td>
                            <td>
                                {{ $debt->propiedad->referencia }}
                                @if($debt->propiedad->estado !== 'activo')
                                    <span class="badge badge-danger ml-1">CORTADO</span>
                                @endif
                            </td>
                            <td>{{ number_format($debt->monto_pendiente, 2) }}</td>
                            <td>{{ $debt->fecha_emision->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge badge-{{ $debt->estado === 'pendiente' ? 'warning' : ($debt->estado === 'pagada' ? 'success' : 'danger') }}">
                                    {{ ucfirst($debt->estado) }}
                                </span>
                            </td>
                            <td>
                                @if($debt->propiedad->estado === 'activo')
                                    <span class="badge badge-success">Activo</span>
                                @elseif($debt->propiedad->estado === 'cortado')
                                    <span class="badge badge-danger">Cortado</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </td>
                            <td width="10px">
                                <a class="btn btn-primary btn-sm" href="{{ route('admin.debts.edit', $debt) }}">
                                    Editar
                                </a>
                            </td>
                            <td width="10px">
                                @if($debt->propiedad->estado === 'activo')
                                    <form action="{{ route('admin.properties.cut', $debt->propiedad) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="btn btn-warning btn-sm" type="button" 
                                                onclick="confirmCut({{ $debt->propiedad->id }}, '{{ $debt->propiedad->referencia }}')">
                                            🚫 Cortar
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.properties.restore', $debt->propiedad) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button class="btn btn-success btn-sm" type="button"
                                                onclick="confirmRestore({{ $debt->propiedad->id }}, '{{ $debt->propiedad->referencia }}')">
                                            ✅ Restaurar
                                        </button>
                                    </form>
                                @endif
                            </td>
                            <td width="10px">
                                <form action="{{ route('admin.debts.destroy', $debt) }}" method="POST" id="delete-form-{{ $debt->id }}">
                                    @csrf @method('delete')
                                    <button class="btn btn-danger btn-sm" type="button" onclick="confirmDelete({{ $debt->id }})">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">
                                @if(request('search'))
                                    No se encontraron deudas para "{{ request('search') }}"
                                @else
                                    No hay deudas registradas
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{ $debts->links() }}
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDelete(id) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "¡No podrá revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    });
}

function confirmCut(propertyId, referencia) {
    Swal.fire({
        title: '¿Cortar servicio?',
        html: `¿Está seguro de cortar el servicio a la propiedad:<br><strong>${referencia}</strong>?<br><br>
               <small class="text-warning">⚠️ Esta propiedad dejará de generar deudas mensuales</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cortar servicio',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const forms = document.querySelectorAll('form[action*="/properties/"]');
            forms.forEach(form => {
                if (form.action.includes(propertyId)) {
                    form.submit();
                }
            });
        }
    });
}

function confirmRestore(propertyId, referencia) {
    Swal.fire({
        title: '¿Restaurar servicio?',
        html: `¿Está seguro de restaurar el servicio a la propiedad:<br><strong>${referencia}</strong>?<br><br>
               <small class="text-success">✅ Esta propiedad volverá a generar deudas mensuales</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, restaurar servicio',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const forms = document.querySelectorAll('form[action*="/properties/"]');
            forms.forEach(form => {
                if (form.action.includes(propertyId)) {
                    form.submit();
                }
            });
        }
    });
}
</script>
@stop