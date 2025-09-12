@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Lista de clientes</h1>
@stop

@section('content')
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
                            <td width="10px"><form action="{{route('admin.clients.destroy', $client)}}" method="POST">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                                </form></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

