@extends('layouts.app')
@section('title','Nuevo producto')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card card-soft">
      <div class="card-body">
        <h5 class="mb-3">Nuevo producto/servicio</h5>
        <form method="post" action="{{ route('products.store') }}">
          @include('products._form')
          <div class="mt-3">
            <button class="btn btn-brand">Guardar</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
