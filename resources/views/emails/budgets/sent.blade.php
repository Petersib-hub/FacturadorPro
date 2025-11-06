@php
    $c        = $budget->client;
    $currency = $budget->currency ?? 'EUR';
    // Logo del tenant con fallback al logo global:
    $logoUrl  = \App\Support\Branding::tenantLogoUrl($budget->user_id);
@endphp
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Presupuesto {{ $budget->number }}</title>
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
            <div class="brand">Presupuesto</div>
        </div>

        <h1>Presupuesto {{ $budget->number }}</h1>
        <div class="muted">Fecha: {{ optional($budget->date)->format('d/m/Y') }} — Cliente: {{ $c->name }}</div>

        <p>Hola {{ $c->name }},</p>
        <p>Adjuntamos el PDF del presupuesto <strong>{{ $budget->number }}</strong>. Si tienes cualquier duda, responde a este email.</p>

        @if($budget->public_token)
            <p>
                <a class="btn btn-brand" href="{{ route('public.budgets.show', $budget->public_token) }}" target="_blank" rel="noopener">
                    Ver presupuesto online
                </a>
            </p>
        @endif

        <div class="grid">
            <div><strong>Total:</strong> {{ number_format($budget->total,2,',','.') }} {{ $currency }}</div>
            <div><strong>Estado:</strong> {{ ucfirst($budget->status) }}</div>
        </div>

        <p class="muted" style="margin-top:16px;">Gracias por tu confianza.</p>
    </div>
</body>
</html>
