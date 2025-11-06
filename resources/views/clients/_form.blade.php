@csrf
<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Nombre*</label>
        <input name="name" class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $client->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">NIF/CIF</label>
        <input name="tax_id" class="form-control @error('tax_id') is-invalid @enderror"
            value="{{ old('tax_id', $client->tax_id ?? '') }}">
        @error('tax_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $client->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input name="phone" class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $client->phone ?? '') }}">
        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <label class="form-label">Dirección</label>
        <input name="address" class="form-control @error('address') is-invalid @enderror"
            value="{{ old('address', $client->address ?? '') }}">
        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">C.P.</label>
        <input name="zip" class="form-control @error('zip') is-invalid @enderror"
            value="{{ old('zip', $client->zip ?? '') }}">
        @error('zip') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-5">
        <label class="form-label">Ciudad</label>
        <input name="city" class="form-control @error('city') is-invalid @enderror"
            value="{{ old('city', $client->city ?? '') }}">
        @error('city') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label class="form-label">País</label>
        <input name="country" class="form-control @error('country') is-invalid @enderror"
            value="{{ old('country', $client->country ?? 'ES') }}">
        @error('country') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" value="1" id="consent" name="consent"
                {{ old('consent') ? 'checked' : '' }}>
            <label class="form-check-label" for="consent">
                Declaro que cuento con el consentimiento del interesado para el tratamiento de sus datos
                conforme a la LOPDGDD y el RGPD, y he informado sobre la finalidad, conservación y derechos de acceso,
                rectificación y supresión.
            </label>
            @error('consent') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        </div>
        <small class="text-muted">Este consentimiento quedará registrado con fecha/hora en el expediente del cliente.</small>
    </div>
</div>
