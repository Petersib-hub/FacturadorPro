@extends('layouts.guest')
@section('title','Crear cuenta')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Crear cuenta</h4>

    <form method="POST" action="{{ route('register') }}" class="row g-3">
      @csrf
      <div class="col-12">
        <label class="form-label" for="name">Nombre</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
               class="form-control @error('name') is-invalid @enderror">
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label" for="password">Contraseña</label>
        <input id="password" type="password" name="password" required
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirmar contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required
               class="form-control">
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Crear mi cuenta</button>
      </div>

      <div class="col-12 text-center">
        <span class="text-muted small">¿Ya tienes cuenta?</span>
        <a href="{{ route('login') }}">Accede aquí</a>
      </div>
    </form>
  </div>
</div>
@endsection
