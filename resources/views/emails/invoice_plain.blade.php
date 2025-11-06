Factura {{ $invoice->number }}

Cliente: {{ $invoice->client->name ?? '—' }}
Fecha: {{ \Illuminate\Support\Carbon::parse($invoice->date)->format('d/m/Y') }}
@isset($invoice->due_date)
Vence: {{ \Illuminate\Support\Carbon::parse($invoice->due_date)->format('d/m/Y') }}
@endisset

Subtotal: {{ number_format($invoice->subtotal,2,',','.') }} {{ $invoice->currency ?? 'EUR' }}
Impuestos: {{ number_format($invoice->tax_total,2,',','.') }} {{ $invoice->currency ?? 'EUR' }}
TOTAL: {{ number_format($invoice->total,2,',','.') }} {{ $invoice->currency ?? 'EUR' }}

Pagado: {{ number_format($invoice->amount_paid ?? 0,2,',','.') }} {{ $invoice->currency ?? 'EUR' }}
Pendiente: {{ number_format(($invoice->total ?? 0) - ($invoice->amount_paid ?? 0),2,',','.') }} {{ $invoice->currency ?? 'EUR' }}

@php
$settings   = \App\Models\UserSetting::firstWhere('user_id', $invoice->user_id);
$showBank   = $settings?->show_bank_on_invoices ?? true;
@endphp
@if($showBank && ($settings?->bank_name || $settings?->bank_holder || $settings?->bank_account))
Datos bancarios
Banco: {{ $settings->bank_name ?? '' }}
Titular: {{ $settings->bank_holder ?? '' }}
Cuenta: {{ $settings->bank_account ?? '' }}
@endif

@isset($settings->billing_notes)
Notas de pago:
{{ $settings->billing_notes }}
@endisset

Gracias por su confianza.
