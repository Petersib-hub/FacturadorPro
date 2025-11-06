@csrf
<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Cliente</label>
        <select name="client_id" class="form-select" required>
            <option value="">— Selecciona —</option>
            @foreach($clients as $c)
            <option value="{{ $c->id }}" @selected(old('client_id', $ri->client_id ?? null)===$c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
        @error('client_id')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label">Frecuencia</label>
        @php $freq = old('frequency', $ri->frequency ?? 'monthly'); @endphp
        <select name="frequency" class="form-select">
            @foreach(['monthly'=>'Mensual','quarterly'=>'Trimestral','yearly'=>'Anual'] as $k=>$v)
            <option value="{{ $k }}" @selected($freq===$k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Moneda</label>
        <input name="currency" class="form-control" value="{{ old('currency',$ri->currency ?? 'EUR') }}" maxlength="3">
    </div>
    <div class="col-md-3">
        <label class="form-label">Inicio</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($ri->start_date ?? null)->toDateString() ?? now()->toDateString()) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Próxima ejecución</label>
        <input type="date" name="next_run_date" class="form-control" value="{{ old('next_run_date', optional($ri->next_run_date ?? null)->toDateString() ?? now()->toDateString()) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Notas públicas (en factura)</label>
        <input name="public_notes" class="form-control" value="{{ old('public_notes',$ri->public_notes ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">Términos</label>
        <textarea name="terms" rows="2" class="form-control">{{ old('terms',$ri->terms ?? '') }}</textarea>
    </div>
</div>

<hr class="my-4">

<div class="d-flex align-items-center justify-content-between mb-2">
    <h6 class="mb-0">Ítems</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddItem">Añadir ítem</button>
</div>

<div class="table-responsive card card-soft">
    <table class="table mb-0" id="itemsTable">
        <thead>
            <tr>
                <th style="min-width:220px">Descripción</th>
                <th class="text-end" style="width:110px">Cant.</th>
                <th class="text-end" style="width:130px">Precio</th>
                <th class="text-end" style="width:110px">Desc.%</th>
                <th class="text-end" style="width:110px">IVA%</th>
                <th style="width:60px"></th>
            </tr>
        </thead>
        <tbody>
            @php
            $oldItems = old('items', isset($ri) ? $ri->items->toArray() : []);
            @endphp
            @forelse($oldItems as $idx=>$it)
            <tr>
                <td><input class="form-control" name="items[{{ $idx }}][description]" value="{{ $it['description'] ?? '' }}" required></td>
                <td><input class="form-control text-end" name="items[{{ $idx }}][quantity]" value="{{ $it['quantity'] ?? '1' }}"></td>
                <td><input class="form-control text-end" name="items[{{ $idx }}][unit_price]" value="{{ $it['unit_price'] ?? '0' }}"></td>
                <td><input class="form-control text-end" name="items[{{ $idx }}][discount]" value="{{ $it['discount'] ?? '0' }}"></td>
                <td><input class="form-control text-end" name="items[{{ $idx }}][tax_rate]" value="{{ $it['tax_rate'] ?? '21' }}"></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btnDel">—</button></td>
            </tr>
            @empty
            <tr>
                <td><input class="form-control" name="items[0][description]" value="" required></td>
                <td><input class="form-control text-end" name="items[0][quantity]" value="1"></td>
                <td><input class="form-control text-end" name="items[0][unit_price]" value="0"></td>
                <td><input class="form-control text-end" name="items[0][discount]" value="0"></td>
                <td><input class="form-control text-end" name="items[0][tax_rate]" value="21"></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btnDel">—</button></td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('head')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const table = document.querySelector('#itemsTable tbody');
        const btnAdd = document.querySelector('#btnAddItem');

        btnAdd?.addEventListener('click', () => {
            const idx = table.querySelectorAll('tr').length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input class="form-control" name="items[${idx}][description]" required></td>
                <td><input class="form-control text-end" name="items[${idx}][quantity]" value="1"></td>
                <td><input class="form-control text-end" name="items[${idx}][unit_price]" value="0"></td>
                <td><input class="form-control text-end" name="items[${idx}][discount]" value="0"></td>
                <td><input class="form-control text-end" name="items[${idx}][tax_rate]" value="21"></td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger btnDel">—</button></td>
                `;
            table.appendChild(tr);
        });

        table?.addEventListener('click', (e) => {
            if (e.target.classList.contains('btnDel')) {
                e.target.closest('tr').remove();
            }
        });
    });
</script>
@endpush
