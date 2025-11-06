@extends('layouts.app')
@section('title','Editar producto')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card card-soft">
      <div class="card-body">
        <h5 class="mb-3">Editar producto/servicio</h5>
        <form method="post" action="{{ route('products.update', $product) }}">
          @csrf @method('PUT')
          @include('products._form', ['product'=>$product])
          <div class="mt-3">
            <button class="btn btn-brand">Actualizar</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
