<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $budget->number }} – Presupuesto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page { margin: 26mm 18mm; }
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; color:#111; }
        .muted{ color:#6c757d } .small{ font-size:11px } .text-right{text-align:right}
        .w-100{ width:100% } .mb-1{ margin-bottom:6px } .mb-2{ margin-bottom:10px } .mb-3{ margin-bottom:16px }
        .table{ width:100%; border-collapse:collapse } .table th,.table td{ padding:8px 10px; border-bottom:1px solid #e9ecef; vertical-align:top }
        .table thead th{ font-weight:700; background:#f8f9fa; border-top:1px solid #e9ecef }
        .totals{ width:45%; margin-left:auto; border-collapse:collapse } .totals td{ padding:8px 10px }
        .totals tr td:first-child{ color:#6c757d } .totals .grand td{ font-size:14px; font-weight:700; border-top:1px solid #dee2e6 }
        header{ border-bottom:2px solid #2fca6c; margin-bottom:18px; padding-bottom:8px } .logo{ height:36px }
        .grid{ display:table; width:100% } .grid .col{ display:table-cell; vertical-align:top } .grid .col-6{ width:50% }
        .box{ border:1px solid #e9ecef; border-radius:8px; padding:10px 12px }
        footer{ position:fixed; bottom:-10mm; left:0; right:0; text-align:center; color:#adb5bd; font-size:11px }
    </style>
</head>
<body>
@php
    $s = \App\Models\UserSetting::firstWhere('user_id', $budget->user_id);
    $issuerName = $s?->display_name ?? ($s?->legal_name ?: 'Mi Empresa');
    $c = $budget->client;
@endphp

@include('pdf.partials.header', ['budget' => $budget])

<section class="grid mb-3">
    <div class="col col-6">
        <div class="mb-1" style="font-weight:700;">Para</div>
        <div class="box">
            <div>{{ $c->name }}</div>
            @if($c->tax_id)<div class="small muted">NIF/CIF: {{ $c->tax_id }}</div>@endif
            @if($c->email)<div class="small muted">Email: {{ $c->email }}</div>@endif
            @if($c->address)<div class="small muted">{{ $c->address }}</div>@endif
            @if($c->city || $c->zip)<div class="small muted">{{ $c->zip }} {{ $c->city }}</div>@endif
            @if($c->country)<div class="small muted">{{ $c->country }}</div>@endif
        </div>
    </div>
    <div class="col col-6">
        <table class="w-100">
            <tr><td class="muted small">Fecha</td><td class="text-right">{{ optional($budget->date)->format('d/m/Y') }}</td></tr>
            <tr><td class="muted small">Válido hasta</td><td class="text-right">{{ optional($budget->due_date)->format('d/m/Y') ?: '—' }}</td></tr>
            <tr><td class="muted small">Moneda</td><td class="text-right">{{ $budget->currency }}</td></tr>
        </table>
    </div>
</section>

<section class="mb-3">
    <table class="table">
        <thead>
            <tr>
                <th>Descripción</th>
                <th class="text-right">Cant.</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Desc. %</th>
                <th class="text-right">IVA %</th>
                <th class="text-right">Total línea</th>
            </tr>
        </thead>
        <tbody>
            @foreach($budget->items as $it)
            <tr>
                <td>{{ $it->description }}</td>
                <td class="text-right">{{ number_format($it->quantity,3,',','.') }}</td>
                <td class="text-right">{{ number_format($it->unit_price,2,',','.') }} €</td>
                <td class="text-right">{{ rtrim(rtrim((string)$it->discount,'0'),'.') }}</td>
                <td class="text-right">{{ rtrim(rtrim((string)$it->tax_rate,'0'),'.') }}</td>
                <td class="text-right">{{ number_format($it->total_line,2,',','.') }} €</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</section>

<section class="mb-3">
    <table class="totals">
        <tr><td>Subtotal</td><td class="text-right">{{ number_format($budget->subtotal,2,',','.') }} €</td></tr>
        <tr><td>Impuestos</td><td class="text-right">{{ number_format($budget->tax_total,2,',','.') }} €</td></tr>
        <tr class="grand"><td>Total</td><td class="text-right">{{ number_format($budget->total,2,',','.') }} €</td></tr>
    </table>
</section>

@if($budget->notes)
<section class="mb-2">
    <div style="font-weight:700;">Notas</div>
    <div class="small">{{ $budget->notes }}</div>
</section>
@endif

@if($budget->terms)
<section class="mb-2">
    <div style="font-weight:700;">Términos</div>
    <div class="small muted">{{ $budget->terms }}</div>
</section>
@endif

<footer>
    @php $settings = \App\Models\UserSetting::firstWhere('user_id', $budget->user_id); @endphp
    {{ config('app.name') }} — generado el {{ now()->format('d/m/Y H:i') }}
    @if($settings?->show_app_branding_on_pdfs) · {{ config('app.url') }} @endif
</footer>
</body>
</html>
