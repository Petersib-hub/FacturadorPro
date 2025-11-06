@extends('layouts.app')
@section('title','Ajustes del negocio')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-soft">
            <div class="card-body">
                <h5 class="mb-3">Ajustes del negocio</h5>

                @if(session('ok')) <div class="alert alert-success">{{ session('ok') }}</div> @endif

                @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
                @endif

                <form method="post" action="{{ route('settings.update') }}" class="row g-3" enctype="multipart/form-data">
                    @csrf

                    <div class="col-12">
                        <label class="form-label">Logotipo (opcional)</label>
                        <div class="d-flex align-items-center gap-3">
                            <input type="file" name="logo" class="form-control" accept="image/*" style="max-width:360px;">
                            @if($settings->logo_path)
                                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt="Logo" style="height:44px;border:1px solid #eee;padding:4px;border-radius:6px;background:#fff;">
                                <div class="form-check ms-2">
                                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                    <label class="form-check-label" for="remove_logo">Eliminar logo</label>
                                </div>
                            @endif
                        </div>
                        <small class="text-muted">PNG/JPG, máx. 2 MB. Se mostrará en PDFs, emails y portal.</small>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Nombre legal</label>
                        <input name="legal_name" class="form-control" value="{{ old('legal_name',$settings->legal_name) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIF/CIF</label>
                        <input name="tax_id" class="form-control" value="{{ old('tax_id',$settings->tax_id) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input name="address" class="form-control" value="{{ old('address',$settings->address) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Código Postal</label>
                        <input name="zip" class="form-control" value="{{ old('zip',$settings->zip) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Ciudad</label>
                        <input name="city" class="form-control" value="{{ old('city',$settings->city) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">País (ISO o nombre)</label>
                        <input name="country" class="form-control" value="{{ old('country',$settings->country ?? 'ES') }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input name="phone" type="tel" class="form-control"
                               value="{{ old('phone', $settings->phone) }}"
                               maxlength="30" placeholder="+34 600 123 456">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Moneda</label>
                        @php $currencies = $currencies ?? ['EUR','USD','MXN','COP','ARS']; @endphp
                        <select name="currency_code" class="form-select">
                            @foreach($currencies as $c)
                                <option value="{{ $c }}" @selected(old('currency_code',$settings->currency_code ?? 'EUR')==$c)>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Idioma (locale)</label>
                        @php $locales = $locales ?? ['es_ES','en_US','ca_ES','eu_ES','gl_ES']; @endphp
                        <select name="locale" class="form-select">
                            @foreach($locales as $loc)
                                <option value="{{ $loc }}" @selected(old('locale',$settings->locale ?? 'es_ES')==$loc)>{{ $loc }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Zona horaria</label>
                        @php $timezones = $timezones ?? ['Europe/Madrid','UTC','America/Mexico_City','America/Bogota','America/Argentina/Buenos_Aires']; @endphp
                        <select name="timezone" class="form-select">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" @selected(old('timezone',$settings->timezone ?? 'Europe/Madrid')==$tz)>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Plantilla PDF</label>
                        <select name="pdf_template" class="form-select">
                            @foreach(['classic','modern','minimal'] as $tpl)
                                <option value="{{ $tpl }}" @selected(old('pdf_template',$settings->pdf_template)==$tpl)>{{ ucfirst($tpl) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12"><hr></div>

                    <div class="col-12">
                        <h6 class="mb-2">Pago por transferencia</h6>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Banco</label>
                                <input name="bank_name" class="form-control" value="{{ old('bank_name',$settings->bank_name) }}" maxlength="120">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Titular de la cuenta</label>
                                <input name="bank_holder" class="form-control" value="{{ old('bank_holder',$settings->bank_holder) }}" maxlength="120">
                            </div>
                        </div>

                        <div class="mb-3 mt-3">
                            <label class="form-label">Cuenta bancaria (IBAN / texto)</label>
                            <input name="bank_account" class="form-control" value="{{ old('bank_account',$settings->bank_account) }}" maxlength="190" placeholder="ES12 3456 7890 1234 5678 9012">
                            <div class="form-text">Se mostrará junto al banco y titular si decides incluirlo en los PDFs.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notas de pago</label>
                            <textarea name="billing_notes" class="form-control" rows="3">{{ old('billing_notes',$settings->billing_notes) }}</textarea>
                            <div class="form-text">Plazos, Bizum, instrucciones, etc. Se muestran bajo los totales.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_bank_on_invoices" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="show_bank_on_invoices" value="1"
                                           {{ old('show_bank_on_invoices', $settings->show_bank_on_invoices) ? 'checked' : '' }}>
                                    <label class="form-check-label">Mostrar datos bancarios en <strong>Facturas</strong></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="show_bank_on_budgets" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="show_bank_on_budgets" value="1"
                                           {{ old('show_bank_on_budgets', $settings->show_bank_on_budgets) ? 'checked' : '' }}>
                                    <label class="form-check-label">Mostrar datos bancarios en <strong>Presupuestos</strong></label>
                                </div>
                                <div class="form-text">Recomendado: desactivado salvo que solicites anticipos.</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12"><hr></div>

                    <div class="col-12">
                        <h6 class="mb-2">Recordatorios de vencimiento</h6>
                        <p class="text-muted mb-2 small">
                            Activa envíos automáticos para facturas enviadas con vencimiento y pendientes de pago.
                        </p>
                    </div>

                    <div class="col-md-3">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="reminders_enabled" value="1"
                                   @checked(old('reminders_enabled',$settings->reminders_enabled))>
                            <label class="form-check-label">Habilitar recordatorios</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Días antes del 1.º recordatorio</label>
                        <input type="number" name="reminder_days_before_first" min="0" max="60" class="form-control"
                               value="{{ old('reminder_days_before_first', $settings->reminder_days_before_first ?? 7) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Días después del vencimiento</label>
                        <input type="number" name="reminder_days_after_due" min="0" max="60" class="form-control"
                               value="{{ old('reminder_days_after_due', $settings->reminder_days_after_due ?? 1) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Repetir cada (días)</label>
                        <input type="number" name="reminder_repeat_every_days" min="1" max="60" class="form-control"
                               value="{{ old('reminder_repeat_every_days', $settings->reminder_repeat_every_days ?? 7) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Máximo recordatorios</label>
                        <input type="number" name="reminder_max_times" min="1" max="12" class="form-control"
                               value="{{ old('reminder_max_times', $settings->reminder_max_times ?? 3) }}">
                    </div>

                    <div class="col-12">
                        <button class="btn btn-brand">Guardar cambios</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-soft">
            <div class="card-body">
                <h6 class="mb-2">Tasas de Impuesto</h6>
                @if($taxRates->isEmpty())
                    <div class="text-muted">Aún no has creado tasas de impuesto.</div>
                @else
                    <ul class="list-group">
                        @foreach($taxRates as $rate)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    {{ $rate->name }}
                                    @if($rate->is_default)
                                        <span class="badge bg-light text-dark ms-1">por defecto</span>
                                    @endif
                                    @if($rate->is_exempt)
                                        <span class="badge bg-warning text-dark ms-1">exento</span>
                                    @endif
                                </span>
                                <span class="badge bg-success rounded-pill">{{ rtrim(rtrim((string)$rate->rate,'0'),'.') }}%</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <small class="text-muted d-block mt-2">
                    Crea/edita tasas en <a href="{{ route('tax-rates.index') }}">Impuestos</a>.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection
