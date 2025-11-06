@php
    // Cuando sea "create", $tax_rate puede no existir
    $model = $tax_rate ?? null;
@endphp

@csrf

<div class="row g-3">

    {{-- Nombre --}}
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input
            name="name"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $model?->name) }}"
            required
        >
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Tasa --}}
    <div class="col-md-3">
        <label class="form-label">Tasa (%)</label>
        <input
            id="rate"
            name="rate"
            type="number"
            step="0.001"
            min="0"
            class="form-control @error('rate') is-invalid @enderror"
            value="{{ old('rate', $model?->rate) }}"
        >
        <div class="form-text">Ej.: 21.000</div>
        @error('rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Checks --}}
    <div class="col-md-3 d-flex align-items-end">
        {{-- Asegurar envío de 0 cuando no se marque --}}
        <input type="hidden" name="is_default" value="0">
        <input type="hidden" name="is_exempt"  value="0">

        <div class="form-check me-4">
            <input
                class="form-check-input"
                type="checkbox"
                name="is_default"
                value="1"
                id="is_default"
                @checked(old('is_default', (bool)($model?->is_default)))
            >
            <label class="form-check-label" for="is_default">Por defecto</label>
        </div>

        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                name="is_exempt"
                value="1"
                id="is_exempt"
                @checked(old('is_exempt', (bool)($model?->is_exempt)))
            >
            <label class="form-check-label" for="is_exempt">Exento</label>
        </div>
    </div>

    {{-- Errores globales (si los hay) --}}
    @if($errors->any())
        <div class="col-12">
            <div class="alert alert-danger mb-0">
                <ul class="mb-0">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const exempt = document.getElementById('is_exempt');
    const rate   = document.getElementById('rate');

    function toggleRate() {
        if (exempt.checked) {
            rate.value = 0;
            rate.setAttribute('readonly', 'readonly');
        } else {
            rate.removeAttribute('readonly');
        }
    }

    exempt?.addEventListener('change', toggleRate);
    toggleRate();
});
</script>
@endpush
