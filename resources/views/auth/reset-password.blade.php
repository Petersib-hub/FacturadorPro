@extends('layouts.guest')
@section('title','Restablecer contraseña')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Restablecer contraseña</h4>

    <form method="POST" action="{{ route('password.store') }}" class="row g-3">
      @csrf

      {{-- Token oculto --}}
      <input type="hidden" name="token" value="{{ $request->route('token') }}">

      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus
               class="form-control @error('email') is-invalid @enderror">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label" for="password">Nueva contraseña</label>
        <input id="password" type="password" name="password" required
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Confirmar nueva contraseña</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control">
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Guardar contraseña</button>
      </div>

      <div class="col-12 text-center">
        <a href="{{ route('login') }}" class="small">Ir al login</a>
      </div>
    </form>
  </div>
</div>
@endsection
