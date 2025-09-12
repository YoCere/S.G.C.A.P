@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Lista de clientes</h1>
@stop

@section('content')
    @if (session('info'))
    <div class="alert alert-success">
        <strong>{{ session('info') }}</strong>
    </div>

    @endif

    <div class="card">
        <div class="card-header">
            <a class="btn btn-primary" href="{{route('admin.clients.create')}}">Agregar cliente</a>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ( $clients as $client)
                        <tr>
                            <td>{{$client->id}}</td>
                            <td>{{$client->nombre}}</td>
                            <td width="10px"><a class="btn btn-primary btn-sm" href="{{route('admin.clients.edit', $client)}}">Editar</a></td>
                            <td width="10px">
                                <form action="{{route('admin.clients.destroy', $client)}}" method="POST" id="delete-form-{{$client->id}}">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger btn-sm" type="button" onclick="confirmDelete({{$client->id}})">Eliminar</button>
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
    function confirmDelete(clientId) {
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
                document.getElementById(`delete-form-${clientId}`).submit();
            }
        });
    }
</script>
@stop

