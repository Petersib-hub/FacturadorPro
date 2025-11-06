@extends('layouts.app')
@section('title','Nuevo cliente')

@section('content')
<div class="row">
  <div class="col-lg-8">
    <div class="card card-soft">
      <div class="card-body">
        <h5 class="mb-3">Nuevo cliente</h5>
        <form method="post" action="{{ route('clients.store') }}">
          @include('clients._form')
          <div class="mt-3">
            <button class="btn btn-brand">Guardar</button>
            <a href="{{ route('clients.index') }}" class="btn btn-outline-secondary">Cancelar</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

