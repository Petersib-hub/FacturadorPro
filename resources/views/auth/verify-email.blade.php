@extends('layouts.guest')
@section('title','Verifica tu email')

@section('content')
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Verificación de email</h4>
    <p class="text-muted">
      Gracias por registrarte. Antes de continuar, revisa tu bandeja de entrada y haz clic en el enlace de verificación.
      Si no has recibido el email, podemos enviarte otro.
    </p>

    @if (session('status') == 'verification-link-sent')
      <div class="alert alert-success">
        Se ha enviado un nuevo enlace de verificación a tu email.
      </div>
    @endif

    <div class="d-flex gap-2">
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button class="btn btn-brand">Reenviar verificación</button>
      </form>

      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="btn btn-outline-secondary">Salir</button>
      </form>
    </div>
  </div>
</div>
@endsection
