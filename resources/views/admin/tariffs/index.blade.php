@extends('adminlte::page')

@section('title', 'Tarifas')

@section('content_header')
    <h1>Lista de tarifas</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <a class="btn btn-primary" href="{{ route('admin.tariffs.create') }}">Agregar tarifa</a>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio (Bs)</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tariffs as $tariff)
                        <tr>
                            <td>{{ $tariff->id }}</td>
                            <td>{{ $tariff->nombre }}</td>
                            <td>{{ number_format($tariff->precio_mensual, 2) }}</td>
                            <td width="10px">
                                <a class="btn btn-primary btn-sm" href="{{ route('admin.tariffs.edit', $tariff) }}">
                                    Editar
                                </a>
                            </td>
                            <td width="10px">
                                <form action="{{ route('admin.tariffs.destroy', $tariff) }}" method="POST" id="delete-form-{{ $tariff->id }}">
                                    @csrf @method('delete')
                                    <button class="btn btn-danger btn-sm" type="button" onclick="confirmDelete({{ $tariff->id }})">
                                        Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
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
