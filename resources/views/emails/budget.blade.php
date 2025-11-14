{{-- resources/views/emails/budget.blade.php --}}
@php
/** @var \App\Models\Budget $budget */
$settings = \App\Models\UserSetting::firstWhere('user_id', $budget->user_id);
$logoUrl  = \App\Support\Branding::tenantLogoUrl($budget->user_id);
$client   = $budget->client;
$currency = $budget->currency ?? 'EUR';

$bankName   = $settings?->bank_name;
$bankHolder = $settings?->bank_holder;
$bankIban   = $settings?->bank_account;
$billing    = $settings?->billing_notes;

$showBank = $settings?->show_bank_on_budgets ?? false;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Presupuesto {{ $budget->number }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
/* estilos mínimos seguros para email */
body{margin:0;padding:0;background:#f6f7fb;font-family:Arial,Helvetica,sans-serif;color:#111}
img{border:0;line-height:100%;outline:none;text-decoration:none;max-width:100%}
.table{width:100%;border-top:1px solid #e9eef5;border-bottom:1px solid #e9eef5}
.table td{padding:8px 0;font-size:14px}
.muted{color:#64748b}
.card{max-width:640px;margin:0 auto;background:#fff;border-radius:8px;border:1px solid #e9eef5;overflow:hidden}
.head{padding:18px 22px;border-bottom:1px solid #e9eef5;display:flex;align-items:center;gap:12px}
.brand{font-weight:700}
.cnt{padding:20px 22px}
.foot{padding:16px 22px;border-top:1px solid #e9eef5;background:#fafbff;font-size:12px;color:#475569}
</style>
</head>
<body>
<div style="display:none;opacity:0;height:0;width:0;overflow:hidden">Presupuesto {{ $budget->number }} para {{ $client?->name }}. Total: {{ number_format($budget->total,2,',','.') }} {{ $currency }}</div>

<div style="padding:20px 12px;background:#f6f7fb">
  <div class="card">
    <div class="head">
      @if($logoUrl)<img src="{{ $logoUrl }}" alt="Logo" style="height:36px;width:auto">@endif
      <div>
        <div class="brand">{{ $settings?->display_name ?? $settings?->legal_name ?? 'Mi Empresa' }}</div>
        @if($settings?->tax_id)<div class="muted" style="font-size:12px">NIF/CIF: {{ $settings->tax_id }}</div>@endif
      </div>
    </div>

    <div class="cnt">
      <h1 style="font-size:18px;margin:0 0 6px">Presupuesto {{ $budget->number }}</h1>
      <div class="muted" style="font-size:12px;margin-bottom:12px">
        @if($client) Para: <strong>{{ $client->name }}</strong>@endif
        @if($budget->date) · Fecha: {{ \Illuminate\Support\Carbon::parse($budget->date)->format('d/m/Y') }} @endif
        @if($budget->due_date) · Válido hasta: {{ \Illuminate\Support\Carbon::parse($budget->due_date)->format('d/m/Y') }} @endif
      </div>

      <table class="table" role="presentation" cellspacing="0" cellpadding="0">
        <tr><td class="muted" style="width:40%">Subtotal</td><td style="text-align:right">{{ number_format($budget->subtotal,2,',','.') }} {{ $currency }}</td></tr>
        <tr><td class="muted">Impuestos</td><td style="text-align:right">{{ number_format($budget->tax_total,2,',','.') }} {{ $currency }}</td></tr>
        <tr><td style="font-weight:700">TOTAL</td><td style="text-align:right;font-weight:700">{{ number_format($budget->total,2,',','.') }} {{ $currency }}</td></tr>
      </table>

      @if($showBank && ($bankName || $bankHolder || $bankIban))
        <div style="margin-top:12px">
          <div style="margin-bottom:8px"><strong>Datos bancarios</strong></div>
          @if($bankName)<div style="margin-bottom:8px">Banco: <strong>{{ $bankName }}</strong></div>@endif
          @if($bankHolder)<div style="margin-bottom:8px">Titular: <strong>{{ $bankHolder }}</strong></div>@endif
          @if($bankIban)<div style="margin-bottom:8px">Cuenta: <strong>{{ $bankIban }}</strong></div>@endif
        </div>
      @endif

      @if($billing)
        <div style="margin-top:12px">
          <div style="margin-bottom:8px"><strong>Notas</strong></div>
          <div style="margin-bottom:8px">{!! nl2br(e($billing)) !!}</div>
        </div>
      @endif
    </div>

    <div class="foot">
      @if($settings?->address)
        {{ $settings->address }} · {{ $settings->zip }} {{ $settings->city }} · {{ $settings->country }}<br>
      @endif
      @if($settings?->email) {{ $settings->email }} @endif
      @if($settings?->email && $settings?->phone) · @endif
      @if($settings?->phone) {{ $settings->phone }} @endif
    </div>
  </div>
</div>
</body>
</html>
