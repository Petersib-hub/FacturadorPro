@extends('layouts.app')
@section('title','Nuevo presupuesto')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body">
                <h5 class="mb-3">Nuevo presupuesto</h5>
                <form method="post" action="{{ route('budgets.store') }}">
                    @include('budgets._form')
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-brand">Guardar</button>
                        <a href="{{ route('budgets.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
