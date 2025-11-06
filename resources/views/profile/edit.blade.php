@extends('layouts.app')
@section('title','Mi perfil')

@section('content')
<div class="row g-3">

    {{-- Columna avatar --}}
    <div class="col-lg-4">
        <div class="card card-soft mb-3">
            <div class="card-body text-center">
                @php
                $avatarUrl = $user->avatar_path
                ? asset('storage/'.$user->avatar_path)
                : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=2fca6c&color=fff&size=128';
                @endphp
                <img src="{{ $avatarUrl }}" alt="Avatar" class="rounded-circle mb-3" style="width:96px;height:96px;object-fit:cover;">

                {{-- errores solo del formulario de foto --}}
                @if ($errors->photo->any())
                <div class="alert alert-danger py-2">
                    <ul class="mb-0">
                        @foreach ($errors->photo->all() as $e)
                        <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="d-grid gap-2">
                    @csrf
                    <input type="file" name="avatar" accept="image/*" class="form-control">
                    <button class="btn btn-brand">Actualizar foto</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Columna datos y contraseña --}}
    <div class="col-lg-8">

        {{-- Datos básicos (PATCH /profile) --}}
        <div class="card card-soft mb-3">
            <div class="card-body">
                <h5 class="mb-3">Información</h5>
                <form method="POST" action="{{ route('profile.update') }}" class="row g-3" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input name="name" class="form-control" value="{{ old('name',$user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" value="{{ old('email',$user->email) }}" required>
                    </div>

                    <div class="col-12">
                        <button class="btn btn-brand">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Cambio de contraseña (por ahora solo UI, requiere método servidor si quieres que funcione) --}}
        <div class="card card-soft mb-3">
            <div class="card-body">
                <h5 class="mb-3">Cambiar contraseña</h5>
                <form method="POST" action="{{ route('profile.update') }}" class="row g-3" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="col-md-4">
                        <label class="form-label">Actual</label>
                        <input type="password" class="form-control" name="current_password" autocomplete="current-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nueva</label>
                        <input type="password" class="form-control" name="password" autocomplete="new-password">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirmar</label>
                        <input type="password" class="form-control" name="password_confirmation" autocomplete="new-password">
                    </div>

                    <div class="col-12">
                        <button class="btn btn-outline-primary">Actualizar contraseña</button>
                    </div>
                </form>
                <small class="text-muted d-block mt-2">
                    Debe tener al menos 8 caracteres, mayúsculas, minúsculas, números y símbolos.
                </small>
            </div>
        </div>

        {{-- Eliminar cuenta (DELETE /profile) --}}
        <div class="card card-soft">
            <div class="card-body">
                <h5 class="mb-2 text-danger">Eliminar cuenta</h5>
                <p class="text-muted small">Esta acción es irreversible. Se cerrará tu sesión y tu cuenta será eliminada.</p>
                <form method="POST" action="{{ route('profile.destroy') }}"
                    onsubmit="return confirm('¿Seguro que deseas eliminar tu cuenta? Esta acción no se puede deshacer.')">
                    @csrf
                    @method('DELETE')
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Confirma con tu contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="col-md-8">
                            <button class="btn btn-outline-danger mt-2 mt-md-0">Eliminar mi cuenta</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
