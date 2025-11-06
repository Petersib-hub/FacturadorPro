@extends('layouts.app')
@section('title','Editar tasa de impuesto')

@section('content')
@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar tasa</h4>
    <a href="{{ route('tax-rates.index') }}" class="btn btn-outline-secondary">Volver</a>
</div>

<div class="card card-soft">
    <div class="card-body">
        <form method="post" action="{{ route('tax-rates.update',$tax_rate) }}">
            @method('PUT')
            @include('tax_rates._form')
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-brand">Guardar cambios</button>
                <a href="{{ route('tax-rates.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
