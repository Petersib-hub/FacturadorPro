@extends('layouts.app')
@section('title','Editar factura')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Editar factura {{ $invoice->number }}</h4>
    <a href="{{ route('invoices.show',$invoice) }}" class="btn btn-outline-secondary">Volver</a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="post" action="{{ route('invoices.update',$invoice) }}" class="row g-3">
    @csrf
    @method('PUT')

    <div class="col-md-6">
        <label class="form-label">Cliente</label>
        <select name="client_id" class="form-select" required>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" @selected(old('client_id',$invoice->client_id)==$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="date" class="form-control" value="{{ old('date',$invoice->date?->toDateString()) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Vence</label>
        <input type="date" name="due_date" class="form-control" value="{{ old('due_date',$invoice->due_date?->toDateString()) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Moneda</label>
        <input name="currency" class="form-control" value="{{ old('currency',$invoice->currency) }}">
    </div>

    <div class="col-12">
        <hr>
        <h6 class="mb-2">Conceptos</h6>
        <small class="text-muted d-block mb-2">Elige un producto guardado para precargar, o escribe manualmente. La descripción es auto-ajustable.</small>
        <div class="table-responsive">
            <table class="table" id="itemsTable">
                <thead>
                    <tr>
                        <th style="width:42%">Descripción</th>
                        <th style="width:18%">Producto guardado</th>
                        <th style="width:8%">Cant.</th>
                        <th style="width:12%">Precio</th>
                        <th style="width:8%">Dto. %</th>
                        <th style="width:8%">IVA %</th>
                        <th style="width:4%"></th>
                    </tr>
                </thead>
                <tbody>
                    @php $oldItems = old('items', $invoice->items->toArray()); @endphp
                    @foreach($oldItems as $idx => $it)
                    <tr>
                        <td>
                            <textarea name="items[{{ $idx }}][description]" class="form-control" rows="2" data-autosize style="resize:vertical;overflow-y:auto" required>{{ $it['description'] ?? '' }}</textarea>
                        </td>
                        <td>
                            <select class="form-select product-picker" data-row="{{ $idx }}">
                                <option value="">— (escribir manual)</option>
                                @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                    data-name="{{ $p->name }}"
                                    data-price="{{ number_format($p->price,2,'.','') }}"
                                    data-tax="{{ number_format($p->tax_rate,2,'.','') }}">
                                    {{ $p->name }}
                                </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input name="items[{{ $idx }}][quantity]" type="number" step="0.001" min="0" class="form-control" value="{{ $it['quantity'] ?? 1 }}"></td>
                        <td><input name="items[{{ $idx }}][unit_price]" type="number" step="0.01" min="0" class="form-control" value="{{ $it['unit_price'] ?? 0 }}"></td>
                        <td><input name="items[{{ $idx }}][discount]" type="number" step="0.01" min="0" class="form-control" value="{{ $it['discount'] ?? 0 }}"></td>
                        <td><input name="items[{{ $idx }}][tax_rate]" type="number" step="0.01" min="0" class="form-control" value="{{ $it['tax_rate'] ?? 0 }}"></td>
                        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">🗑</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">Añadir línea</button>
    </div>

    <div class="col-12">
        <label class="form-label">Notas</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes',$invoice->notes) }}</textarea>
    </div>
    <div class="col-12">
        <label class="form-label">Términos</label>
        <textarea name="terms" class="form-control" rows="2">{{ old('terms',$invoice->terms) }}</textarea>
    </div>

    <div class="col-12">
        <button class="btn btn-brand">Guardar cambios</button>
    </div>
</form>

@push('scripts')
<script>
    // Autosize
    function autosize(el) {
        el.style.height = 'auto';
        el.style.overflowY = 'hidden';
        el.style.height = el.scrollHeight + 'px';
        if (el.scrollHeight > 240) {
            el.style.overflowY = 'auto';
            el.style.maxHeight = '320px';
        }
    }

    function initAutosize(scope = document) {
        scope.querySelectorAll('textarea[data-autosize]').forEach(ta => {
            ta.addEventListener('input', () => autosize(ta));
            autosize(ta);
        });
    }
    document.addEventListener('DOMContentLoaded', () => initAutosize(document));

    // Relleno por producto
    function bindPickers(scope = document) {
        scope.querySelectorAll('.product-picker').forEach(sel => {
            sel.addEventListener('change', () => {
                const opt = sel.options[sel.selectedIndex];
                const row = sel.dataset.row;
                if (!opt || !opt.value) return; // manual
                const name = opt.dataset.name,
                    price = opt.dataset.price,
                    tax = opt.dataset.tax;
                const tr = sel.closest('tr');
                tr.querySelector(`textarea[name="items[${row}][description]"]`).value = name;
                tr.querySelector(`input[name="items[${row}][unit_price]"]`).value = price;
                tr.querySelector(`input[name="items[${row}][tax_rate]"]`).value = tax;
                autosize(tr.querySelector(`textarea[name="items[${row}][description]"]`));
            });
        });
    }
    bindPickers(document);

    // Dinámica
    function addRow() {
        const tbody = document.querySelector('#itemsTable tbody');
        const idx = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
        <td><textarea name="items[${idx}][description]" class="form-control" rows="2" data-autosize style="resize:vertical;overflow-y:auto" required></textarea></td>
        <td>
        <select class="form-select product-picker" data-row="${idx}">
            <option value="">— (escribir manual)</option>
            @foreach($products as $p)
            <option value="{{ $p->id }}" data-name="{{ $p->name }}" data-price="{{ number_format($p->price,2,'.','') }}" data-tax="{{ number_format($p->tax_rate,2,'.','') }}">{{ $p->name }}</option>
            @endforeach
        </select>
        </td>
        <td><input name="items[${idx}][quantity]" type="number" step="0.001" min="0" class="form-control" value="1"></td>
        <td><input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" class="form-control" value="0"></td>
        <td><input name="items[${idx}][discount]" type="number" step="0.01" min="0" class="form-control" value="0"></td>
        <td><input name="items[${idx}][tax_rate]" type="number" step="0.01" min="0" class="form-control" value="0"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">🗑</button></td>`;
        tbody.appendChild(tr);
        initAutosize(tr);
        bindPickers(tr);
    }

    function removeRow(btn) {
        btn.closest('tr').remove();
    }
</script>
@endpush
@endsection
