Presupuesto {{ $budget->number }}

Cliente: {{ $budget->client->name ?? '—' }}
Fecha: {{ \Illuminate\Support\Carbon::parse($budget->date)->format('d/m/Y') }}
@isset($budget->due_date)
Válido hasta: {{ \Illuminate\Support\Carbon::parse($budget->due_date)->format('d/m/Y') }}
@endisset

Subtotal: {{ number_format($budget->subtotal,2,',','.') }} {{ $budget->currency ?? 'EUR' }}
Impuestos: {{ number_format($budget->tax_total,2,',','.') }} {{ $budget->currency ?? 'EUR' }}
TOTAL: {{ number_format($budget->total,2,',','.') }} {{ $budget->currency ?? 'EUR' }}

@php
$settings   = \App\Models\UserSetting::firstWhere('user_id', $budget->user_id);
$showBank   = $settings?->show_bank_on_budgets ?? false;
@endphp
@if($showBank && ($settings?->bank_name || $settings?->bank_holder || $settings?->bank_account))
Datos bancarios
Banco: {{ $settings->bank_name ?? '' }}
Titular: {{ $settings->bank_holder ?? '' }}
Cuenta: {{ $settings->bank_account ?? '' }}
@endif

@isset($settings->billing_notes)
Notas:
{{ $settings->billing_notes }}
@endisset

Gracias por su confianza.
