@extends('layouts.app')
@section('title','Recurrente')

@section('content')
@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

@php
    $freqMap = ['daily'=>'Diaria','weekly'=>'Semanal','monthly'=>'Mensual','quarterly'=>'Trimestral','yearly'=>'Anual'];
    $statusMap = ['active'=>'Activo','paused'=>'Pausado'];
@endphp

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0">Recurrente — {{ $ri->client?->name }}</h4>

    <div>
        <a href="{{ route('recurring-invoices.edit',$ri) }}" class="btn btn-sm btn-outline-secondary">Editar</a>
        <form class="d-inline" method="post" action="{{ route('recurring-invoices.duplicate',$ri) }}">
            @csrf
            <button class="btn btn-sm btn-outline-primary">Duplicar</button>
        </form>
        @if($ri->status === 'active')
            <form class="d-inline" method="post" action="{{ route('recurring-invoices.pause',$ri) }}">
                @csrf @method('PUT')
                <button class="btn btn-sm btn-outline-warning">Pausar</button>
            </form>
        @else
            <form class="d-inline" method="post" action="{{ route('recurring-invoices.resume',$ri) }}">
                @csrf @method('PUT')
                <button class="btn btn-sm btn-outline-success">Reanudar</button>
            </form>
        @endif
        <form class="d-inline" method="post" action="{{ route('recurring-invoices.run-now',$ri) }}">
            @csrf
            <button class="btn btn-sm btn-brand">Generar ahora</button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card card-soft h-100">
            <div class="card-body">
                <div class="mb-2"><strong>Cliente:</strong> {{ $ri->client?->name }}</div>
                <div class="mb-2"><strong>Frecuencia:</strong> {{ $freqMap[$ri->frequency] ?? ucfirst($ri->frequency) }}</div>
                <div class="mb-2"><strong>Próxima ejecución:</strong> {{ optional($ri->next_run_date)->format('d/m/Y') }}</div>
                <div class="mb-2"><strong>Estado:</strong> {{ $statusMap[$ri->status] ?? ucfirst($ri->status) }}</div>
                <div class="mb-2"><strong>Moneda:</strong> {{ $ri->currency }}</div>
                @if($ri->public_notes)
                    <div class="mb-2"><strong>Notas:</strong> {{ $ri->public_notes }}</div>
                @endif
                @if($ri->terms)
                    <div class="mb-2"><strong>Términos:</strong> {{ $ri->terms }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card card-soft h-100">
            <div class="card-body">
                <h6 class="mb-3">Ítems</h6>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Descripción</th>
                                <th class="text-end">Cant.</th>
                                <th class="text-end">Precio</th>
                                <th class="text-end">IVA%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ri->items as $it)
                                <tr>
                                    <td style="white-space:pre-wrap;word-break:break-word;overflow-wrap:anywhere">{{ $it->description }}</td>
                                    <td class="text-end">{{ number_format($it->quantity,3,',','.') }}</td>
                                    <td class="text-end">{{ number_format($it->unit_price,2,',','.') }} €</td>
                                    <td class="text-end">{{ rtrim(rtrim((string)$it->tax_rate,'0'),'.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">Sin ítems.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
