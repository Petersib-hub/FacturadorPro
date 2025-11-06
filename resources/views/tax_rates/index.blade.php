@extends('layouts.app')
@section('title','Tasas de impuestos')

@section('content')
{{--  @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif --}}

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Tasas de impuestos</h4>
    <a href="{{ route('tax-rates.create') }}" class="btn btn-brand">Nueva tasa</a>
</div>

<form class="input-group mb-3" method="get" action="{{ route('tax-rates.index') }}">
    <input name="q" class="form-control" placeholder="Buscar por nombre" value="{{ $q }}">
    <button class="btn btn-outline-secondary">Buscar</button>
</form>

<div class="card card-soft">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th class="text-end">Tasa</th>
                    <th class="text-center">Predeterminada</th>
                    <th class="text-center">Exenta</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $rate)
                <tr>
                    <td class="fw-medium">{{ $rate->name }}</td>
                    <td class="text-end">
                        {{ rtrim(rtrim(number_format((float)$rate->rate,3,',','.'), '0'), ',') }}%
                    </td>
                    <td class="text-center">
                        @if($rate->is_default)
                        <span class="badge bg-success-subtle text-success">Sí</span>
                        @else
                        <span class="badge bg-light text-muted">No</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($rate->is_exempt)
                        <span class="badge bg-info-subtle text-info">Exenta</span>
                        @else
                        <span class="badge bg-light text-muted">No</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('tax-rates.edit', $rate) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
                        <form class="d-inline" method="post" action="{{ route('tax-rates.destroy', $rate) }}"
                            onsubmit="return confirm('¿Eliminar esta tasa?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">No hay tasas registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $list->links() }}
</div>
@endsection
