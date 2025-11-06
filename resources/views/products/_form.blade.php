@csrf
<div class="row g-3">
  <div class="col-md-8">
    <label class="form-label">Nombre*</label>
    <input name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $product->name ?? '') }}" required>
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
  <div class="col-md-4">
    <label class="form-label">Precio unitario (€)*</label>
    <input name="unit_price" type="number" step="0.01" min="0"
           class="form-control @error('unit_price') is-invalid @enderror"
           value="{{ old('unit_price', isset($product)?$product->unit_price:'0.00') }}" required>
    @error('unit_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-md-6">
    <label class="form-label">Impuesto (selecciona una tasa)</label>
    <select name="tax_rate_id" class="form-select @error('tax_rate_id') is-invalid @enderror">
      <option value="">— Selecciona —</option>
      @foreach(($taxRates ?? []) as $t)
        <option value="{{ $t->id }}" data-rate="{{ $t->rate }}"
          @selected(old('tax_rate_id', $product->tax_rate_id ?? null) == $t->id)>
          {{ $t->name }} ({{ rtrim(rtrim($t->rate,'0'),'.') }}%)
          @if($t->is_default) — predeterminado @endif
        </option>
      @endforeach
    </select>
    @error('tax_rate_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <small class="text-muted">Guardaremos el porcentaje actual de la tasa como “snapshot”.</small>
  </div>
  <div class="col-md-2">
    <label class="form-label">IVA %</label>
    <input name="tax_rate" type="number" step="0.001" min="0"
           class="form-control @error('tax_rate') is-invalid @enderror"
           value="{{ old('tax_rate', $product->tax_rate ?? ($taxRates->firstWhere('is_default',true)->rate ?? 0)) }}">
    @error('tax_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  <div class="col-12">
    <label class="form-label">Descripción</label>
    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description ?? '') }}</textarea>
    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>
</div>

@push('scripts')
<script>
// Si eligen una tasa, rellenamos el campo IVA % automáticamente
document.addEventListener('DOMContentLoaded', function(){
  const select = document.querySelector('select[name="tax_rate_id"]');
  const inputRate = document.querySelector('input[name="tax_rate"]');
  if(select && inputRate){
    select.addEventListener('change', ()=>{
      const opt = select.options[select.selectedIndex];
      const rate = opt?.getAttribute('data-rate');
      if(rate !== null) inputRate.value = rate;
    });
  }
});
</script>
@endpush
