@extends('layouts.app')
@section('title','Nueva factura')

@section('content')
<div class="card card-soft">
    <div class="card-body">
        <h5 class="mb-3">Nueva factura</h5>
        <form method="post" action="{{ route('invoices.store') }}">
            @include('invoices._form')
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-brand">Guardar</button>
                <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
@endsection
