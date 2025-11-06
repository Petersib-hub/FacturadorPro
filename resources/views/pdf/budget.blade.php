<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>{{ $budget->number }} – Presupuesto</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @page {
            margin: 26mm 18mm;
        }

        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .brand {
            color: #2fca6c;
        }

        .muted {
            color: #6c757d;
        }

        .small {
            font-size: 11px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .w-100 {
            width: 100%;
        }

        .mb-0 {
            margin-bottom: 0;
        }

        .mb-1 {
            margin-bottom: 6px;
        }

        .mb-2 {
            margin-bottom: 10px;
        }

        .mb-3 {
            margin-bottom: 16px;
        }

        .mb-4 {
            margin-bottom: 22px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: top;
        }

        .table thead th {
            font-weight: 700;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }

        .totals {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .totals td {
            padding: 8px 10px;
        }

        .totals tr td:first-child {
            color: #6c757d;
        }

        .totals .grand td {
            font-size: 14px;
            font-weight: 700;
            border-top: 1px solid #dee2e6;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            font-size: 11px;
            color: #495057;
        }

        header {
            border-bottom: 2px solid #2fca6c;
            margin-bottom: 18px;
            padding-bottom: 8px;
        }

        .logo {
            height: 36px;
        }

        .grid {
            display: table;
            width: 100%;
        }

        .grid .col {
            display: table-cell;
            vertical-align: top;
        }

        .grid .col-6 {
            width: 50%;
        }

        .grid .col-7 {
            width: 58%;
        }

        .grid .col-5 {
            width: 42%;
        }

        .box {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 10px 12px;
        }

        footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            text-align: center;
            color: #adb5bd;
            font-size: 11px;
        }

        .sign-row {
            display: table;
            width: 100%;
            margin-top: 28px;
        }

        .sign-col {
            display: table-cell;
            width: 50%;
            vertical-align: bottom;
            padding: 0 8px;
        }

        .sign-line {
            border-top: 1px solid #adb5bd;
            height: 42px;
        }

        .sign-label {
            margin-top: 6px;
            font-size: 11px;
            color: #6c757d;
            text-align: center;
        }
    </style>
</head>

<body>
    @php
    // Emisor (tenant)
    $s = \App\Models\UserSetting::firstWhere('user_id', $document->user_id ?? ($invoice->user_id ?? $budget->user_id));
    $issuerName = $s?->display_name ?? ($s?->legal_name ?: 'Mi Empresa');
    $issuerTax = $s?->tax_id;
    $issuerAdr1 = $s?->address;
    $issuerAdr2 = trim(($s?->zip ? $s->zip.' ' : '').($s?->city ?? ''));
    $issuerCtry = $s?->country;
    $logoPath = \App\Support\Branding::tenantLogoDiskPathForPdf($s?->user_id);

    // Cliente
    $c = isset($invoice) ? $invoice->client : $budget->client;
    @endphp

    @include('pdf.partials.header', ['invoice' => $invoice ?? null, 'budget' => $budget ?? null])

    <!--<header>
    <table class="w-100">
        <tr>
            <td style="width:60%;vertical-align:top;">
                <div style="display:flex;gap:12px;align-items:center;">
                    <img src="{{ $logoPath }}" class="logo" alt="Logo">
                    <div>
                        <div class="brand" style="font-weight:800; font-size:18px;">{{ $issuerName }}</div>
                        @if($issuerTax)<div class="small muted">NIF/CIF: {{ $issuerTax }}</div>@endif
                        @if($issuerAdr1)<div class="small muted">{{ $issuerAdr1 }}</div>@endif
                        @if($issuerAdr2)<div class="small muted">{{ $issuerAdr2 }}</div>@endif
                        @if($issuerCtry)<div class="small muted">{{ $issuerCtry }}</div>@endif
                    </div>
                </div>
            </td>
            <td class="text-right" style="width:40%;vertical-align:top;">
                <div style="font-size:22px; font-weight:800;">
                    {{ isset($invoice) ? 'FACTURA' : 'PRESUPUESTO' }}
                </div>
                <div class="muted">{{ isset($invoice) ? $invoice->number : $budget->number }}</div>
                <div class="badge" style="margin-top:4px;">
                    {{ ucfirst(isset($invoice) ? $invoice->status : $budget->status) }}
                </div>
                <table class="w-100" style="margin-top:10px;">
                    <tr>
                        <td class="muted small">Fecha</td>
                        <td class="text-right">{{ optional(isset($invoice)?$invoice->date:$budget->date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted small">{{ isset($invoice)?'Vence':'Válido hasta' }}</td>
                        <td class="text-right">{{ optional(isset($invoice)?$invoice->due_date:$budget->due_date)->format('d/m/Y') ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="muted small">Moneda</td>
                        <td class="text-right">{{ isset($invoice)?$invoice->currency:$budget->currency }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="margin-top:14px;border-top:3px solid #2fca6c;padding-top:12px;"></div>

    <table class="w-100">
        <tr>
            <td style="width:50%;vertical-align:top;">
                <div class="small muted mb-1">Cliente</div>
                <div style="font-weight:700">{{ $c->name }}</div>
                @if($c->tax_id)<div class="small muted">NIF/CIF: {{ $c->tax_id }}</div>@endif
                @if($c->email)<div class="small muted">Email: {{ $c->email }}</div>@endif
                @if($c->phone)<div class="small muted">Tel.: {{ $c->phone }}</div>@endif
                @if($c->address)<div class="small muted">{{ $c->address }}</div>@endif
                @if($c->zip || $c->city)<div class="small muted">{{ $c->zip }} {{ $c->city }}</div>@endif
                @if($c->country)<div class="small muted">{{ $c->country }}</div>@endif
            </td>
            <td style="width:50%;vertical-align:top;">
                @php $bank = $s?->bank_account; $bill = $s?->billing_notes; @endphp
                @if($bank || $bill)
                    <div class="small muted mb-1">Datos de pago</div>
                    @if($bank)<div class="small"><strong>IBAN/Cuenta:</strong> {{ $bank }}</div>@endif
                    @if($bill)<div class="small muted">{{ $bill }}</div>@endif
                @endif
            </td>
        </tr>
    </table>
</header>-->


    <section class="grid mb-3">
        <div class="col col-6">
            <div class="mb-1" style="font-weight:700;">Para</div>
            <div class="box">
                <div>{{ $budget->client->name }}</div>
                @if($budget->client->tax_id)<div class="small muted">NIF/CIF: {{ $budget->client->tax_id }}</div>@endif
                @if($budget->client->email)<div class="small muted">Email: {{ $budget->client->email }}</div>@endif
                @if($budget->client->address)<div class="small muted">{{ $budget->client->address }}</div>@endif
                @if($budget->client->city || $budget->client->zip)
                <div class="small muted">{{ $budget->client->zip }} {{ $budget->client->city }}</div>
                @endif
                @if($budget->client->country)
                <div class="small muted">{{ $budget->client->country }}</div>
                @endif
            </div>
        </div>
        <div class="col col-6">
            <table class="w-100">
                <tr>
                    <td class="muted small">Fecha</td>
                    <td class="text-right">{{ optional($budget->date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td class="muted small">Válido hasta</td>
                    <td class="text-right">{{ optional($budget->due_date)->format('d/m/Y') ?: '—' }}</td>
                </tr>
                <tr>
                    <td class="muted small">Moneda</td>
                    <td class="text-right">{{ $budget->currency }}</td>
                </tr>
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
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ number_format($budget->subtotal,2,',','.') }} €</td>
            </tr>
            <tr>
                <td>Impuestos</td>
                <td class="text-right">{{ number_format($budget->tax_total,2,',','.') }} €</td>
            </tr>
            <tr class="grand">
                <td>Total</td>
                <td class="text-right">{{ number_format($budget->total,2,',','.') }} €</td>
            </tr>
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

    {{-- Firmas --}}

    <section class="mb-3" style="margin-top:18px;">
        <table class="w-100" style="border-collapse:separate;border-spacing:0;">
            <tr>
                <td style="width:50%;padding-right:10px;">
                    <div class="small muted mb-1">Firma del emisor</div>
                    <div style="height:70px;border:1px dashed #cbd5e1;border-radius:8px;"></div>
                    <div class="small muted" style="margin-top:6px;">{{ $issuerName }}</div>
                </td>
                <td style="width:50%;padding-left:10px;">
                    <div class="small muted mb-1">Firma del receptor</div>
                    <div style="height:70px;border:1px dashed #cbd5e1;border-radius:8px;"></div>
                    <div class="small muted" style="margin-top:6px;">{{ $c->name }}</div>
                </td>
            </tr>
        </table>
    </section>

    <footer>
        @php $settings = \App\Models\UserSetting::firstWhere('user_id', ($invoice->user_id ?? $budget->user_id)); @endphp
        {{ config('app.name') }} — generado el {{ now()->format('d/m/Y H:i') }}
        @if($settings?->show_app_branding_on_pdfs)
        · {{ config('app.url') }}
        @endif
    </footer>

</body>

</html>
