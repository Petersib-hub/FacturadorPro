@extends('layouts.guest')
@section('title','Confirmar contraseña')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Confirma tu contraseña</h4>
    <p class="text-muted">Por seguridad, confirma tu contraseña para continuar.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="row g-3">
      @csrf
      <div class="col-12">
        <label class="form-label" for="password">Contraseña</label>
        <input id="password" type="password" name="password" required
               class="form-control @error('password') is-invalid @enderror" autocomplete="current-password">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Confirmar</button>
      </div>

      <div class="col-12 text-center">
        <a href="{{ route('login') }}" class="small">Volver al login</a>
      </div>
    </form>
  </div>
</div>
@endsection
