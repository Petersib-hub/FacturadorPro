@extends('layouts.public')
@section('title','Factura '.$invoice->number)

@push('head')
<style>
    .desc-cell {
        white-space: pre-wrap;
        word-break: break-word;
        overflow-wrap: anywhere
    }
</style>
@endpush

@includeIf('verifactu._status_badge', ['invoice' => $invoice])
@includeIf('verifactu._qr', ['invoice' => $invoice])


@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Factura {{ $invoice->number }}</h4>
    <a href="{{ route('invoices.pdf',$invoice) }}" class="btn btn-success" target="_blank" rel="noopener">Imprimir / Guardar PDF</a>
</div>

@php
$statusMap=[
'draft'=>['label'=>'Borrador','class'=>'bg-secondary'],
'pending'=>['label'=>'Pendiente','class'=>'bg-warning text-dark'],
'sent'=>['label'=>'Enviada','class'=>'bg-info text-dark'],
'paid'=>['label'=>'Pagada','class'=>'bg-success'],
'void'=>['label'=>'Anulada','class'=>'bg-dark'],
];
$sLabel=$statusMap[$invoice->status]['label']??ucfirst($invoice->status);
$sClass=$statusMap[$invoice->status]['class']??'bg-light text-dark';
@endphp

<div class="card card-soft mb-3">
    <div class="card-body">
        <div class="row gy-2">
            <div class="col-md-6">
                <div><strong>Cliente:</strong> {{ $invoice->client->name }}</div>
                @if($invoice->client->email)<div><strong>Email:</strong> {{ $invoice->client->email }}</div>@endif
                @if($invoice->client->tax_id)<div><strong>NIF/CIF:</strong> {{ $invoice->client->tax_id }}</div>@endif
            </div>
            <div class="col-md-6">
                <div><strong>Fecha:</strong> {{ optional($invoice->date)->format('d/m/Y') }}</div>
                <div><strong>Vence:</strong> {{ optional($invoice->due_date)->format('d/m/Y') ?? '—' }}</div>
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
                    @foreach($invoice->items as $it)
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
            <div><strong>Subtotal:</strong> {{ number_format($invoice->subtotal,2,',','.') }} €</div>
            <div><strong>Impuestos:</strong> {{ number_format($invoice->tax_total,2,',','.') }} €</div>
            <div class="fs-5"><strong>Total:</strong> {{ number_format($invoice->total,2,',','.') }} €</div>
        </div>
    </div>
</div>
@endsection
