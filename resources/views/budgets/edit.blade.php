@extends('layouts.app')
@section('title','Editar presupuesto')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-soft">
            <div class="card-body">
                <h5 class="mb-3">Editar presupuesto {{ $budget->number }}</h5>
                <form method="post" action="{{ route('budgets.update',$budget) }}">
                    @csrf @method('PUT')
                    @include('budgets._form', ['budget'=>$budget])
                    <div class="mt-3 d-flex gap-2">
                        <button class="btn btn-brand">Actualizar</button>
                        <a href="{{ route('budgets.show',$budget) }}" class="btn btn-outline-secondary">Ver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection