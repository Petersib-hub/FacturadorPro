@php
    $c        = $invoice->client;
    $currency = $invoice->currency ?? 'EUR';
    // Logo del tenant con fallback al logo global:
    $logoUrl  = \App\Support\Branding::tenantLogoUrl($invoice->user_id);
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura {{ $invoice->number }}</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body {
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: #f7f7f9;
            margin: 0;
            padding: 24px;
            color: #1f2937;
        }
        .card {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            border: 1px solid #eef1f5;
        }
        h1 { font-size: 20px; margin: 0 0 8px; }
        .muted { color: #6b7280; font-size: 14px; }
        .btn { display: inline-block; padding: 10px 16px; border-radius: 10px; text-decoration: none; }
        .btn-brand { background: #2fca6c; color: white; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; }
        .header {
            display:flex; align-items:center; gap:10px; margin-bottom:12px; padding-bottom:10px;
            border-bottom: 3px solid #2fca6c;
        }
        .header img { height: 28px; display:block; }
        .brand { font-weight:800; font-size:16px; color:#2fca6c; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <img src="{{ $logoUrl }}" alt="Logo">
            <div class="brand">Factura</div>
        </div>

        <h1>Factura {{ $invoice->number }}</h1>
        <div class="muted">Fecha: {{ optional($invoice->date)->format('d/m/Y') }} — Cliente: {{ $c->name }}</div>

        <p>Hola {{ $c->name }},</p>
        <p>Adjuntamos el PDF de la factura <strong>{{ $invoice->number }}</strong>. Si ya has realizado el pago, por favor ignora este mensaje.</p>

        @if($invoice->public_token)
            <p>
                <a class="btn btn-brand" href="{{ route('public.invoices.show', $invoice->public_token) }}" target="_blank" rel="noopener">
                    Ver factura online
                </a>
            </p>
        @endif

        <div class="grid">
            <div><strong>Total:</strong> {{ number_format($invoice->total,2,',','.') }} {{ $currency }}</div>
            <div><strong>Estado:</strong> {{ ucfirst($invoice->status) }}</div>
            <div><strong>Vencimiento:</strong> {{ optional($invoice->due_date)->format('d/m/Y') ?? '—' }}</div>
            <div><strong>Pendiente:</strong> {{ number_format(max(0,$invoice->total - $invoice->amount_paid),2,',','.') }} {{ $currency }}</div>
        </div>

        <p class="muted" style="margin-top:16px;">Gracias por tu preferencia.</p>
    </div>
</body>
</html>
