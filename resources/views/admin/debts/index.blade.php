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
        <div class="card-header">
            <a class="btn btn-primary" href="{{ route('admin.debts.create') }}">Registrar deuda</a>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Propiedad</th>
                        <th>Monto (Bs)</th>
                        <th>Fecha emisión</th>
                        <th>Estado</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debts as $debt)
                        <tr>
                            <td>{{ $debt->id }}</td>
                            <td>{{ $debt->propiedad->cliente->nombre }}</td>
                            <td>{{ $debt->propiedad->referencia }}</td>
                            <td>{{ number_format($debt->monto_pendiente, 2) }}</td>
                            <td>{{ $debt->fecha_emision }}</td>
                            <td>
                                <span class="badge badge-{{ $debt->estado === 'pendiente' ? 'warning' : ($debt->estado === 'pagada' ? 'success' : 'danger') }}">
                                    {{ ucfirst($debt->estado) }}
                                </span>
                            </td>
                            <td width="10px">
                                <a class="btn btn-primary btn-sm" href="{{ route('admin.debts.edit', $debt) }}">
                                    Editar
                                </a>
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
                    @endforeach
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
</script>
@stop
