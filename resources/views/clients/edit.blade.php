@extends('layouts.app')
@section('title','Editar cliente')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-soft">
            <div class="card-body">
                <h5 class="mb-3">Editar cliente</h5>
                <form method="post" action="{{ route('clients.update', $client) }}">
                    @csrf @method('PUT')
                    @include('clients._form', ['client'=>$client])
                    <div class="mt-3">
                        <button class="btn btn-brand">Actualizar</button>
                        <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
