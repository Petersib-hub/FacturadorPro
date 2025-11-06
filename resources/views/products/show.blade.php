@extends('layouts.app')
@section('title','Producto')

@section('content')
<div class="card card-soft">
  <div class="card-body">
    <h5 class="mb-3">{{ $product->name }}</h5>
    <div class="row">
      <div class="col-md-6">
        <div class="mb-2"><strong>Precio:</strong> {{ number_format($product->unit_price,2,',','.') }} €</div>
        <div class="mb-2"><strong>IVA:</strong> {{ rtrim(rtrim($product->tax_rate,'0'),'.') }}%</div>
      </div>
      <div class="col-md-6">
        <div class="mb-2"><strong>Tasa:</strong> {{ $product->taxRate?->name ?? '—' }}</div>
        <div class="mb-2"><strong>Creado el:</strong> {{ $product->created_at->format('d/m/Y H:i') }}</div>
      </div>
      <div class="col-12 mt-2">
        <strong>Descripción:</strong>
        <p class="mb-0">{{ $product->description ?: '—' }}</p>
      </div>
    </div>
  </div>
</div>
@endsection
