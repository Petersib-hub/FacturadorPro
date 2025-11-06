@extends('layouts.public')
@section('title','Presupuesto '.$budget->number)

@push('head')
<style>
    .desc-cell {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Presupuesto {{ $budget->number }}</h4>
    <a href="{{ route('budgets.pdf',$budget) }}" class="btn btn-success" target="_blank" rel="noopener">Imprimir / Guardar PDF</a>
</div>

@php
$statusMap=[
'draft'=>['label'=>'Borrador','class'=>'bg-secondary'],
'sent'=>['label'=>'Enviado','class'=>'bg-info text-dark'],
'accepted'=>['label'=>'Aceptado','class'=>'bg-success'],
'rejected'=>['label'=>'Rechazado','class'=>'bg-danger'],
];
$sLabel=$statusMap[$budget->status]['label']??ucfirst($budget->status);
$sClass=$statusMap[$budget->status]['class']??'bg-light text-dark';
@endphp

<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row gy-2">
            <div class="col-md-6">
                <div><strong>Cliente:</strong> {{ $budget->client->name }}</div>
                @if($budget->client->email)<div><strong>Email:</strong> {{ $budget->client->email }}</div>@endif
                @if($budget->client->tax_id)<div><strong>NIF/CIF:</strong> {{ $budget->client->tax_id }}</div>@endif
            </div>
            <div class="col-md-6">
                <div><strong>Fecha:</strong> {{ optional($budget->date)->format('d/m/Y') }}</div>
                <div><strong>Válido hasta:</strong> {{ optional($budget->due_date)->format('d/m/Y') ?? '—' }}</div>
                <div><strong>Estado:</strong> <span class="badge {{ $sClass }}">{{ $sLabel }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card card-soft mb-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="desc-cell">Descripción</th>
                        <th class="text-end">Cant.</th>
                        <th class="text-end">Precio</th>
                        <th class="text-end">Desc. %</th>
                        <th class="text-end">IVA %</th>
                        <th class="text-end">Total línea</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($budget->items as $it)
                    <tr>
                        <td class="desc-cell">{{ $it->description }}</td>
                        <td class="text-end">{{ number_format($it->quantity,3,',','.') }}</td>
                        <td class="text-end">{{ number_format($it->unit_price,2,',','.') }} €</td>
                        <td class="text-end">{{ rtrim(rtrim((string)$it->discount,'0'),'.') }}</td>
                        <td class="text-end">{{ rtrim(rtrim((string)$it->tax_rate,'0'),'.') }}</td>
                        <td class="text-end">{{ number_format($it->total_line,2,',','.') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3 text-end">
            <div><strong>Subtotal:</strong> {{ number_format($budget->subtotal,2,',','.') }} €</div>
            <div><strong>Impuestos:</strong> {{ number_format($budget->tax_total,2,',','.') }} €</div>
            <div class="fs-5"><strong>Total:</strong> {{ number_format($budget->total,2,',','.') }} €</div>
        </div>
    </div>
</div>
@endsection
