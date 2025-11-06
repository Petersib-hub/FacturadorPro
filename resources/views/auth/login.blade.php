@extends('layouts.guest')
@section('title','Acceder')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Acceder</h4>

    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="row g-3">
      @csrf
      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12">
        <label class="form-label" for="password">Contraseña</label>
        <input id="password" type="password" name="password" required
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-12 d-flex justify-content-between align-items-center">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember" name="remember">
          <label class="form-check-label" for="remember">Recuérdame</label>
        </div>
        @if (Route::has('password.request'))
          <a href="{{ route('password.request') }}" class="small">¿Olvidaste la contraseña?</a>
        @endif
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Entrar</button>
      </div>

      @if (Route::has('register'))
      <div class="col-12 text-center">
        <span class="text-muted small">¿No tienes cuenta?</span>
        <a href="{{ route('register') }}">Crear cuenta</a>
      </div>
      @endif
    </form>
  </div>
</div>
@endsection
