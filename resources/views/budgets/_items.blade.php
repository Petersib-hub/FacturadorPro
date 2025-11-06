@php
  $rows = old('items', $items ?? [
    ['product_id'=>'','description'=>'','quantity'=>1,'unit_price'=>0,'tax_rate'=>0,'discount'=>0],
  ]);
@endphp

<div id="items-wrapper" class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr>
        <th style="width:28%">Descripción</th>
        <th style="width:12%">Cant.</th>
        <th style="width:14%">Precio</th>
        <th style="width:12%">IVA %</th>
        <th style="width:12%">Desc. %</th>
        <th style="width:14%" class="text-end">Total línea</th>
        <th style="width:8%"></th>
      </tr>
    </thead>
    <tbody id="items-body">
      @foreach($rows as $i => $row)
      <tr>
        <td>
          <input name="items[{{ $i }}][description]" class="form-control" placeholder="Descripción"
                 value="{{ $row['description'] ?? '' }}" required>
        </td>
        <td>
          <input type="number" step="0.001" min="0.001" name="items[{{ $i }}][quantity]" class="form-control calc"
                 value="{{ $row['quantity'] ?? 1 }}" required>
        </td>
        <td>
          <input type="number" step="0.01" min="0" name="items[{{ $i }}][unit_price]" class="form-control calc"
                 value="{{ $row['unit_price'] ?? 0 }}" required>
        </td>
        <td>
          <input type="number" step="0.001" min="0" name="items[{{ $i }}][tax_rate]" class="form-control calc"
                 value="{{ $row['tax_rate'] ?? 0 }}" required>
        </td>
        <td>
          <input type="number" step="0.001" min="0" max="100" name="items[{{ $i }}][discount]" class="form-control calc"
                 value="{{ $row['discount'] ?? 0 }}">
        </td>
        <td class="text-end">
          <span class="line-total">0.00</span> €
        </td>
        <td class="text-end">
          <button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
</div>

<div class="d-flex justify-content-between">
  <button type="button" class="btn btn-outline-secondary" id="add-row">Añadir línea</button>
  <div class="text-end">
    <div><strong>Subtotal:</strong> <span id="subtotal">0.00</span> €</div>
    <div><strong>Impuestos:</strong> <span id="tax_total">0.00</span> €</div>
    <div class="fs-5"><strong>Total:</strong> <span id="grand_total">0.00</span> €</div>
  </div>
</div>

@push('scripts')
<script>
(function(){
  const body = document.getElementById('items-body');
  let idx = {{ count($rows) }};

  function recalc(){
    let subtotal=0, tax_total=0, grand=0;
    body.querySelectorAll('tr').forEach(tr=>{
      const q = parseFloat(tr.querySelector('[name*="[quantity]"]').value || 0);
      const p = parseFloat(tr.querySelector('[name*="[unit_price]"]').value || 0);
      const t = parseFloat(tr.querySelector('[name*="[tax_rate]"]').value || 0);
      const d = parseFloat(tr.querySelector('[name*="[discount]"]').value || 0);
      const base = q*p;
      const disc = base*(d/100);
      const afterDisc = base - disc;
      const tax = afterDisc*(t/100);
      const line = afterDisc + tax;
      subtotal += afterDisc;
      tax_total += tax;
      grand += line;
      tr.querySelector('.line-total').textContent = line.toFixed(2);
    });
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax_total').textContent = tax_total.toFixed(2);
    document.getElementById('grand_total').textContent = grand.toFixed(2);
  }

  document.getElementById('add-row').addEventListener('click', ()=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input name="items[${idx}][description]" class="form-control" placeholder="Descripción" required></td>
      <td><input type="number" step="0.001" min="0.001" name="items[${idx}][quantity]" class="form-control calc" value="1" required></td>
      <td><input type="number" step="0.01" min="0" name="items[${idx}][unit_price]" class="form-control calc" value="0" required></td>
      <td><input type="number" step="0.001" min="0" name="items[${idx}][tax_rate]" class="form-control calc" value="0" required></td>
      <td><input type="number" step="0.001" min="0" max="100" name="items[${idx}][discount]" class="form-control calc" value="0"></td>
      <td class="text-end"><span class="line-total">0.00</span> €</td>
      <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-row">&times;</button></td>
    `;
    body.appendChild(tr);
    idx++;
    recalc();
  });

  body.addEventListener('input', e=>{
    if(e.target.classList.contains('calc')) recalc();
  });

  body.addEventListener('click', e=>{
    if(e.target.classList.contains('remove-row')){
      e.target.closest('tr').remove();
      recalc();
    }
  });

  recalc();
})();
</script>
@endpush
