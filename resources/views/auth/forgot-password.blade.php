@extends('layouts.guest')
@section('title','Recuperar contraseña')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">¿Olvidaste tu contraseña?</h4>
    <p class="text-muted">Ingresa tu email y te enviaremos un enlace para restablecerla.</p>

    @if (session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="row g-3">
      @csrf
      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Enviar enlace</button>
      </div>

      <div class="col-12 text-center">
        <a href="{{ route('login') }}" class="small">Volver al login</a>
      </div>
    </form>
  </div>
</div>
@endsection
