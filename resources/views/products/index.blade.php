@extends('layouts.app')
@section('title','Productos')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
  <h4 class="mb-0">Productos/Servicios</h4>
  <a href="{{ route('products.create') }}" class="btn btn-brand">Nuevo producto</a>
</div>

@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

<form class="input-group mb-3" method="get">
  <input name="q" class="form-control" placeholder="Buscar por nombre o descripción" value="{{ $q }}">
  <button class="btn btn-outline-secondary">Buscar</button>
</form>

<div class="row g-3">
  @forelse($products as $p)
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card card-soft h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between">
            <h6 class="mb-1">{{ $p->name }}</h6>
            <span class="badge bg-light text-dark">{{ number_format($p->unit_price,2,',','.') }} €</span>
          </div>
          <div class="small text-muted mb-2">
            IVA: {{ rtrim(rtrim($p->tax_rate,'0'),'.') }}%
          </div>
          <p class="mb-0 small text-muted" style="min-height:2.2rem;">
            {{ \Illuminate\Support\Str::limit($p->description, 120) }}
          </p>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-between">
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('products.show',$p) }}">Ver</a>
          <div>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('products.edit',$p) }}">Editar</a>
            <form class="d-inline" method="post" action="{{ route('products.destroy',$p) }}" onsubmit="return confirm('¿Eliminar producto?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger">Eliminar</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @empty
    <div class="col-12"><div class="alert alert-info">Aún no hay productos.</div></div>
  @endforelse
</div>

<div class="mt-3">
  {{ $products->links() }}
</div>
@endsection
