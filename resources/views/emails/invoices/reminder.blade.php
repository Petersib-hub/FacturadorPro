<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recordatorio — {{ $invoice->number }}</title>
</head>
<body style="margin:0;padding:0;background:#f6f7f9;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111;">
    @php
        $logoUrl = \App\Support\Branding::tenantLogoUrl($invoice->user_id);
        $publicUrl = route('public.invoices.show', $invoice->public_token);
        $pending = max(0, ($invoice->total ?? 0) - ($invoice->amount_paid ?? 0));
    @endphp

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f7f9;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.04);">
                    <tr>
                        <td style="padding:18px 22px;border-bottom:3px solid #2fca6c;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="{{ $logoUrl }}" alt="Logo" style="height:28px;">
                                <div style="font-weight:800;font-size:16px;color:#2fca6c;">Recordatorio de pago</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px;">
                            <h2 style="margin:0 0 8px 0;font-size:20px;">Factura {{ $invoice->number }}</h2>
                            <p style="margin:0 0 6px 0;color:#6c757d;">
                                Hola {{ $invoice->client->name }}, este es un recordatorio de pago para la factura
                                <strong>{{ $invoice->number }}</strong>.
                            </p>
                            <p style="margin:0 0 12px 0;color:#6c757d;">
                                Vencimiento: <strong>{{ optional($invoice->due_date)->format('d/m/Y') ?: '—' }}</strong>
                                @if($pending>0) · Pendiente: <strong>{{ number_format($pending,2,',','.') }} {{ $invoice->currency }}</strong> @endif
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="width:100%;margin:16px 0;background:#f8f9fa;border-radius:10px;">
                                <tr>
                                    <td style="padding:14px 16px;">
                                        <div style="display:flex;flex-wrap:wrap;gap:16px;">
                                            <div style="min-width:220px;">
                                                <div style="color:#6c757d;font-size:12px;">Fecha</div>
                                                <div style="font-weight:600;">{{ optional($invoice->date)->format('d/m/Y') }}</div>
                                            </div>
                                            <div style="min-width:220px;">
                                                <div style="color:#6c757d;font-size:12px;">Vence</div>
                                                <div style="font-weight:600;">{{ optional($invoice->due_date)->format('d/m/Y') ?: '—' }}</div>
                                            </div>
                                            <div style="min-width:220px;">
                                                <div style="color:#6c757d;font-size:12px;">Total</div>
                                                <div style="font-weight:800;">{{ number_format($invoice->total,2,',','.') }} {{ $invoice->currency }}</div>
                                            </div>
                                            <div style="min-width:220px;">
                                                <div style="color:#6c757d;font-size:12px;">Pendiente</div>
                                                <div style="font-weight:800;">{{ number_format($pending,2,',','.') }} {{ $invoice->currency }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 18px 0;">
                                <a href="{{ $publicUrl }}" style="display:inline-block;background:#2fca6c;color:#fff;text-decoration:none;padding:10px 16px;border-radius:10px;font-weight:700;">
                                    Ver factura online
                                </a>
                            </p>

                            <p style="margin:18px 0 0 0;color:#6c757d;font-size:13px;">
                                Este email se envía automáticamente como recordatorio. Si ya realizaste el pago, ignora este mensaje.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 22px;border-top:1px solid #eef0f2;color:#adb5bd;font-size:12px;text-align:center;">
                            © {{ date('Y') }} {{ config('app.name') }}
                        </td>
                    </tr>
                </table>

                <div style="color:#adb5bd;font-size:12px;margin-top:10px;">
                    Enviado por {{ config('app.name') }}
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
