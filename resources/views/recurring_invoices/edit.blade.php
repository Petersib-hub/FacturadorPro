@extends('layouts.app')
@section('title','Editar recurrente')

@section('content')
@if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="post" action="{{ route('recurring-invoices.update',$ri) }}">
    @csrf
    @method('PUT')

    <div class="card card-soft mb-3">
        <div class="card-body">
            <h5 class="mb-3">Editar plantilla</h5>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Cliente</label>
                    <select name="client_id" class="form-select" required>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" @selected(old('client_id',$ri->client_id)==$c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Frecuencia</label>
                    @php $freqs = ['daily'=>'Diaria','weekly'=>'Semanal','monthly'=>'Mensual','quarterly'=>'Trimestral','yearly'=>'Anual']; @endphp
                    <select name="frequency" class="form-select" required>
                        @foreach($freqs as $k=>$lbl)
                            <option value="{{ $k }}" @selected(old('frequency',$ri->frequency)==$k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Moneda</label>
                    <input name="currency" class="form-control" value="{{ old('currency',$ri->currency) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Inicio</label>
                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date',$ri->start_date?->toDateString()) }}" disabled>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Próxima ejecución</label>
                    <input type="date" name="next_run_date" class="form-control" value="{{ old('next_run_date',$ri->next_run_date?->toDateString()) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label">Términos</label>
                    <textarea name="terms" rows="2" class="form-control">{{ old('terms',$ri->terms) }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label">Notas públicas (en factura)</label>
                    <input name="public_notes" class="form-control" value="{{ old('public_notes',$ri->public_notes) }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <h6 class="mb-2">Ítems</h6>
            <small class="text-muted d-block mb-2">
                Descripción como <em>textarea</em> auto-ajustable. Puedes seleccionar un producto para precargar precio/IVA.
            </small>

            <div class="table-responsive">
                <table class="table align-middle" id="riItemsTable">
                    <thead>
                        <tr>
                            <th style="width:22%">Producto (opcional)</th>
                            <th style="width:38%">Descripción</th>
                            <th style="width:10%">Cant.</th>
                            <th style="width:12%">Precio</th>
                            <th style="width:8%">Desc.%</th>
                            <th style="width:10%">IVA %</th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rows = old('items') ?? $ri->items->map(fn($i)=>[
                                'product_id'=>$i->product_id,
                                'description'=>$i->description,
                                'quantity'=>$i->quantity,
                                'unit_price'=>$i->unit_price,
                                'discount'=>$i->discount,
                                'tax_rate'=>$i->tax_rate,
                            ])->toArray();
                        @endphp
                        @foreach($rows as $idx=>$it)
                            <tr>
                                <td>
                                    <select name="items[{{ $idx }}][product_id]" class="form-select prods">
                                        <option value="">— Seleccionar —</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}"
                                                data-price="{{ (float)$p->unit_price }}"
                                                data-tax="{{ (float)$p->tax_rate }}"
                                                @selected(($it['product_id'] ?? null) == $p->id)>
                                                {{ $p->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <textarea name="items[{{ $idx }}][description]" class="form-control desc" rows="1" style="min-height:38px;resize:vertical;overflow:auto;" required>{{ $it['description'] ?? '' }}</textarea>
                                </td>
                                <td><input name="items[{{ $idx }}][quantity]" type="number" step="0.001" min="0" class="form-control qty" value="{{ $it['quantity'] ?? 1 }}"></td>
                                <td><input name="items[{{ $idx }}][unit_price]" type="number" step="0.01" min="0" class="form-control price" value="{{ $it['unit_price'] ?? 0 }}"></td>
                                <td><input name="items[{{ $idx }}][discount]" type="number" step="0.01" min="0" class="form-control disc" value="{{ $it['discount'] ?? 0 }}"></td>
                                <td><input name="items[{{ $idx }}][tax_rate]" type="number" step="0.01" min="0" class="form-control tax" value="{{ $it['tax_rate'] ?? 0 }}"></td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="riRemoveRow(this)">—</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button class="btn btn-sm btn-outline-primary" type="button" onclick="riAddRow()">Añadir ítem</button>

            <div class="row mt-4">
                <div class="col-lg-4 ms-auto">
                    <div class="p-3 border rounded-3 bg-light">
                        <div class="d-flex justify-content-between">
                            <div class="text-muted">Subtotal</div>
                            <div><strong id="riSub">0,00</strong> <span id="riCur1">EUR</span></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <div class="text-muted">Impuestos</div>
                            <div><strong id="riTax">0,00</strong> <span id="riCur2">EUR</span></div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fs-5">
                            <div><strong>Total</strong></div>
                            <div><strong id="riTot">0,00</strong> <span id="riCur3">EUR</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-brand">Guardar cambios</button>
                <a href="{{ route('recurring-invoices.show',$ri) }}" class="btn btn-outline-secondary">Volver</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
@include('recurring_invoices.partials.items-script', ['products'=>$products])
@endpush
