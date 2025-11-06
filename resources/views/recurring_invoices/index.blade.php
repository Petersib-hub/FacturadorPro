{{-- resources/views/recurring_invoices/index.blade.php --}}
@extends('layouts.app')
@section('title','Recurrentes')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Facturas recurrentes</h4>
    <a href="{{ route('recurring-invoices.create') }}" class="btn btn-brand">Nueva plantilla</a>
</div>

@if(session('ok'))<div class="alert alert-success">{{ session('ok') }}</div>@endif

<form class="row g-2 mb-3" method="get">
    <div class="col-md-4">
        <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Buscar por cliente…">
    </div>
    <div class="col-md-3">
        <select class="form-select" name="status">
            <option value="">— Estado —</option>
            @foreach(['active'=>'Activa','paused'=>'Pausada','ended'=>'Finalizada'] as $k=>$v)
                <option value="{{ $k }}" @selected(($status ?? '')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select class="form-select" name="frequency">
            <option value="">— Frecuencia —</option>
            @foreach(['monthly'=>'Mensual','quarterly'=>'Trimestral','yearly'=>'Anual'] as $k=>$v)
                <option value="{{ $k }}" @selected(($freq ?? '')===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">Filtrar</button>
    </div>
</form>

<div class="card card-soft">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Frecuencia</th>
                    <th>Siguiente</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
            @forelse($list as $ri)
                <tr>
                    <td><a href="{{ route('recurring-invoices.show',$ri) }}">{{ $ri->client?->name }}</a></td>
                    <td>{{ ucfirst($ri->frequency) }}</td>
                    <td>{{ optional($ri->next_run_date)->format('d/m/Y') }}</td>
                    <td><span class="badge bg-light text-dark">{{ ucfirst($ri->status) }}</span></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('recurring-invoices.edit',$ri) }}">Editar</a>

                        {{-- ACTÍVALO cuando exista la ruta
                        <form class="d-inline" method="post" action="{{ route('recurring-invoices.duplicate',$ri) }}">@csrf
                            <button class="btn btn-sm btn-outline-primary">Duplicar</button>
                        </form>
                        --}}

                        <form class="d-inline" method="post" action="{{ route('recurring-invoices.destroy',$ri) }}"
                            onsubmit="return confirm('¿Eliminar plantilla?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No hay plantillas todavía.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $list->links() }}</div>
@endsection
