@extends('layouts.app')
@section('title','Tasa')

@section('content')
<div class="card card-soft">
    <div class="card-body">
        <h5 class="mb-3">Tasa de impuesto</h5>
        <div class="row g-3">
            <div class="col-md-6"><strong>Nombre:</strong> {{ $tax_rate->name }}</div>
            <div class="col-md-3"><strong>Tasa:</strong> {{ number_format($tax_rate->rate,3,',','.') }} %</div>
            <div class="col-md-3"><strong>Exenta:</strong> {{ $tax_rate->is_exempt ? 'Sí' : 'No' }}</div>
            <div class="col-md-3"><strong>Por defecto:</strong> {{ $tax_rate->is_default ? 'Sí' : 'No' }}</div>
        </div>
        <div class="mt-3">
            <a class="btn btn-outline-secondary" href="{{ route('tax-rates.edit',$tax_rate) }}">Editar</a>
            <a class="btn btn-outline-primary" href="{{ route('tax-rates.index') }}">Volver</a>
        </div>
    </div>
</div>
@endsection
