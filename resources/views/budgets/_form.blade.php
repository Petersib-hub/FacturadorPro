{{-- resources/views/budgets/_form.blade.php --}}
@csrf
@php
    $cur = old('currency', $budget->currency ?? ($settings->currency_code ?? 'EUR'));
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Cliente</label>
        <select name="client_id" class="form-select" required>
            @foreach(($clients ?? []) as $c)
                <option value="{{ $c->id }}" @selected(old('client_id', $budget->client_id ?? null) == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha</label>
        <input type="date" name="date" class="form-control"
               value="{{ old('date', ($budget->date ?? now())->toDateString()) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Válido hasta</label>
        <input type="date" name="due_date" class="form-control"
               value="{{ old('due_date', ($budget->due_date ?? now()->addDays(14))->toDateString()) }}">
    </div>

    <div class="col-md-3">
        <label class="form-label">Moneda</label>
        <input name="currency" class="form-control" value="{{ $cur }}">
    </div>
</div>

<hr>

<h6 class="mb-2">Conceptos</h6>
<small class="text-muted d-block mb-2">
    Puedes escribir la descripción manualmente o elegir un producto guardado para precargarla.
    La descripción es auto-ajustable.
</small>

<div class="table-responsive">
    <table class="table align-middle" id="itemsTable">
        <thead>
        <tr>
            <th style="width:40%">Descripción</th>
            <th style="width:18%">Producto guardado</th>
            <th style="width:8%">Cant.</th>
            <th style="width:12%">Precio</th>
            <th style="width:8%">Dto. %</th>
            <th style="width:8%">IVA %</th>
            <th style="width:6%"></th>
        </tr>
        </thead>
        <tbody>
        @php
            $oldItems = old('items', isset($budget)
                ? $budget->items->toArray()
                : [['description'=>'','quantity'=>1,'unit_price'=>0,'discount'=>0,'tax_rate'=>0]]);
        @endphp
        @foreach($oldItems as $idx => $it)
            <tr>
                <td>
                    <textarea name="items[{{ $idx }}][description]" class="form-control desc"
                              rows="1" style="min-height:38px;resize:vertical;overflow:auto;"
                              required>{{ $it['description'] ?? '' }}</textarea>
                </td>

                <td>
                    <select class="form-select product-picker" data-row="{{ $idx }}">
                        <option value="">— (escribir manual)</option>
                        @foreach(($products ?? []) as $p)
                            <option value="{{ $p->id }}"
                                    data-name="{{ $p->name }}"
                                    data-price="{{ number_format($p->price,2,'.','') }}"
                                    data-tax="{{ number_format($p->tax_rate,2,'.','') }}">
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                </td>

                <td><input name="items[{{ $idx }}][quantity]" type="number" step="0.001" min="0" class="form-control qty" value="{{ $it['quantity'] ?? 1 }}"></td>
                <td><input name="items[{{ $idx }}][unit_price]" type="number" step="0.01" min="0" class="form-control price" value="{{ $it['unit_price'] ?? 0 }}"></td>
                <td><input name="items[{{ $idx }}][discount]" type="number" step="0.01" min="0" class="form-control disc" value="{{ $it['discount'] ?? 0 }}"></td>
                <td><input name="items[{{ $idx }}][tax_rate]" type="number" step="0.01" min="0" class="form-control tax" value="{{ $it['tax_rate'] ?? 0 }}"></td>
                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Eliminar</button></td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">Añadir línea</button>

<hr class="mt-4">

<div class="row g-3">
    <div class="col-lg-8">
        <label class="form-label">Notas</label>
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $budget->notes ?? '') }}</textarea>

        <label class="form-label mt-3">Términos</label>
        <textarea name="terms" class="form-control" rows="2">{{ old('terms', $budget->terms ?? '') }}</textarea>
    </div>

    <div class="col-lg-4">
        <div class="p-3 border rounded-3 bg-light">
            <div class="d-flex justify-content-between">
                <div class="text-muted">Subtotal</div>
                <div><strong id="sumSubtotal">0,00</strong> <span id="sumCur">€</span></div>
            </div>
            <div class="d-flex justify-content-between">
                <div class="text-muted">Impuestos</div>
                <div><strong id="sumTax">0,00</strong> <span id="sumCur2">€</span></div>
            </div>
            <hr>
            <div class="d-flex justify-content-between fs-5">
                <div><strong>Total</strong></div>
                <div><strong id="sumTotal">0,00</strong> <span id="sumCur3">€</span></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const tbody = document.getElementById('itemsTable').querySelector('tbody');
    const currencyInput = document.querySelector('input[name="currency"]');
    const curEls = [document.getElementById('sumCur'), document.getElementById('sumCur2'), document.getElementById('sumCur3')];

    function fmt(n){
        return (isNaN(n)?0:n).toLocaleString('es-ES',{minimumFractionDigits:2, maximumFractionDigits:2});
    }

    function recalc(){
        let subtotal=0, tax=0;
        tbody.querySelectorAll('tr').forEach(tr=>{
            const q = parseFloat(tr.querySelector('.qty')?.value || 0);
            const p = parseFloat(tr.querySelector('.price')?.value || 0);
            const d = parseFloat(tr.querySelector('.disc')?.value || 0);
            const t = parseFloat(tr.querySelector('.tax')?.value || 0);
            const net = q * p * (1 - (d/100));
            const tAmt = net * (t/100);
            subtotal += net;
            tax += tAmt;
        });
        document.getElementById('sumSubtotal').innerText = fmt(subtotal);
        document.getElementById('sumTax').innerText = fmt(tax);
        document.getElementById('sumTotal').innerText = fmt(subtotal + tax);
        const cur = (currencyInput?.value || '€');
        curEls.forEach(el=> el.innerText = ' ' + cur);
    }

    // autosize
    function autosize(el){
        el.style.height = 'auto';
        el.style.height = el.scrollHeight + 'px';
        if (el.scrollHeight > 320){ el.style.maxHeight='320px'; el.style.overflowY='auto'; }
    }

    function hookRow(scope){
        scope.querySelectorAll('.qty,.price,.disc,.tax').forEach(i=> i.addEventListener('input', recalc));
        scope.querySelectorAll('textarea.desc').forEach(t=>{ autosize(t); t.addEventListener('input',()=>autosize(t)); });
        // selector de producto
        scope.querySelectorAll('.product-picker').forEach(sel=>{
            sel.addEventListener('change',()=>{
                const opt = sel.options[sel.selectedIndex];
                if(!opt || !opt.value) return; // manual
                const row = sel.dataset.row;
                const tr  = sel.closest('tr');
                tr.querySelector(`textarea[name="items[${row}][description]"]`).value = opt.dataset.name || '';
                tr.querySelector(`input[name="items[${row}][unit_price]"]`).value    = opt.dataset.price || 0;
                tr.querySelector(`input[name="items[${row}][tax_rate]"]`).value      = opt.dataset.tax || 0;
                autosize(tr.querySelector(`textarea[name="items[${row}][description]"]`));
                recalc();
            });
        });
    }

    window.addRow = function(){
        const idx = tbody.querySelectorAll('tr').length;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><textarea name="items[${idx}][description]" class="form-control desc" rows="1" style="min-height:38px;resize:vertical;overflow:auto;" required></textarea></td>
            <td>
                <select class="form-select product-picker" data-row="${idx}">
                    <option value="">— (escribir manual)</option>
                    @foreach(($products ?? []) as $p)
                        <option value="{{ $p->id }}"
                                data-name="{{ $p->name }}"
                                data-price="{{ number_format($p->price,2,'.','') }}"
                                data-tax="{{ number_format($p->tax_rate,2,'.','') }}">
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td><input name="items[${idx}][quantity]" type="number" step="0.001" min="0" class="form-control qty" value="1"></td>
            <td><input name="items[${idx}][unit_price]" type="number" step="0.01" min="0" class="form-control price" value="0"></td>
            <td><input name="items[${idx}][discount]" type="number" step="0.01" min="0" class="form-control disc" value="0"></td>
            <td><input name="items[${idx}][tax_rate]" type="number" step="0.01" min="0" class="form-control tax" value="0"></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)">Eliminar</button></td>
        `;
        tbody.appendChild(tr);
        hookRow(tr);
        recalc();
    };

    window.removeRow = function(btn){
        btn.closest('tr').remove();
        recalc();
    };

    // init
    hookRow(tbody);
    currencyInput?.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
