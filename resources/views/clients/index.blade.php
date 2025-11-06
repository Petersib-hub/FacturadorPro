@extends('layouts.app')
@section('title','Clientes')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Clientes</h4>
    <a href="{{ route('clients.create') }}" class="btn btn-brand">Nuevo cliente</a>
</div>

@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<form class="input-group mb-3" method="get">
    <input name="q" class="form-control" placeholder="Buscar por nombre, email o NIF" value="{{ $q ?? request('q') }}">
    <button class="btn btn-outline-secondary">Buscar</button>
</form>

<style>
.table-actions-wrapper { position: relative; }
.table-actions-wrapper .dropdown-menu { position: absolute; right: 0; }
.table-responsive { overflow: visible; }
</style>

<div class="table-responsive card card-soft">
    <table class="table mb-0">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Email</th>
                <th>NIF/CIF</th>
                <th>Ciudad</th>
                <th class="text-end">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $c)
            <tr>
                <td>{{ $c->name }}</td>
                <td>{{ $c->email }}</td>
                <td>{{ $c->tax_id }}</td>
                <td>{{ $c->city }}</td>
                <td class="text-end table-actions-wrapper">
                    <div class="btn-group">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('clients.show',$c) }}">Ver</a>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown"></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('clients.edit',$c) }}">Editar</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form class="d-inline" method="post" action="{{ route('clients.destroy',$c) }}"
                                      onsubmit="return confirm('¿Eliminar cliente?')">
                                    @csrf @method('DELETE')
                                    <button class="dropdown-item text-danger">Eliminar</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted py-4">Aún no hay clientes.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3">
    {{ $clients->links() }}
</div>
@endsection
