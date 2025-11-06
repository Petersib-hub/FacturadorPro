{{-- resources/views/emails/budget.blade.php --}}
@php
/** @var \App\Models\Budget $budget */
$settings = \App\Models\UserSetting::firstWhere('user_id', $budget->user_id);
$logoUrl = \App\Support\Branding::tenantLogoUrl($budget->user_id);
$client = $budget->client;
$currency = $budget->currency ?? 'EUR';

$bankName = $settings?->bank_name;
$bankHolder = $settings?->bank_holder;
$bankIban = $settings?->bank_account;
$billing = $settings?->billing_notes;

$showBank = $settings?->show_bank_on_budgets ?? false;
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Presupuesto {{ $budget->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: #f6f7fb;
            font-family: Arial, Helvetica, sans-serif;
            color: #111;
        }

        img {
            border: 0;
            line-height: 100%;
            outline: none;
            text-decoration: none;
            max-width: 100%;
        }

        table {
            border-collapse: collapse !important;
        }

        .preheader {
            display: none !important;
            visibility: hidden;
            opacity: 0;
            overflow: hidden;
            height: 0;
            width: 0;
            color: transparent;
        }

        .wrap {
            width: 100%;
            background: #f6f7fb;
            padding: 20px 12px;
        }

        .card {
            max-width: 640px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e9eef5;
        }

        .head {
            padding: 18px 22px;
            border-bottom: 1px solid #e9eef5;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .head .brand {
            font-weight: 700;
            font-size: 16px;
        }

        .head .sub {
            color: #64748b;
            font-size: 12px;
        }

        .cnt {
            padding: 20px 22px;
        }

        .h1 {
            font-size: 18px;
            margin: 0 0 6px 0;
        }

        .muted {
            color: #64748b;
            font-size: 12px;
        }

        .kb {
            background: #0f172a;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            padding: 10px 14px;
            display: inline-block;
            font-weight: 700
        }

        .table {
            width: 100%;
            margin-top: 12px;
            border-top: 1px solid #e9eef5;
            border-bottom: 1px solid #e9eef5
        }

        .table td {
            padding: 8px 0;
            font-size: 14px;
        }

        .table .label {
            color: #64748b;
            width: 40%;
        }

        .total {
            font-weight: 700
        }

        .foot {
            padding: 16px 22px;
            border-top: 1px solid #e9eef5;
            background: #fafbff;
            font-size: 12px;
            color: #475569;
        }

        .mb-8 {
            margin-bottom: 8px
        }

        .mb-12 {
            margin-bottom: 12px
        }

        .mt-12 {
            margin-top: 12px
        }
    </style>
</head>

<body>
    <div class="preheader">Presupuesto {{ $budget->number }} para {{ $client?->name }}. Total: {{ number_format($budget->total,2,',','.') }} {{ $currency }}</div>

    <div class="wrap">
        <div class="card">
            <div class="head">
                @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" style="height:36px;width:auto;">
                @endif
                <div>
                    <div class="brand">{{ $settings?->display_name ?? $settings?->legal_name ?? 'Mi Empresa' }}</div>
                    @if($settings?->tax_id)<div class="sub">NIF/CIF: {{ $settings->tax_id }}</div>@endif
                </div>
            </div>

            <div class="cnt">
                <h1 class="h1">Presupuesto {{ $budget->number }}</h1>
                <div class="muted mb-12">
                    @if($client) Para: <strong>{{ $client->name }}</strong>@endif
                    @if($budget->date) · Fecha: {{ \Illuminate\Support\Carbon::parse($budget->date)->format('d/m/Y') }} @endif
                    @if($budget->due_date) · Válido hasta: {{ \Illuminate\Support\Carbon::parse($budget->due_date)->format('d/m/Y') }} @endif
                </div>

                <table class="table" role="presentation" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class="label">Subtotal</td>
                        <td class="value" style="text-align:right">{{ number_format($budget->subtotal,2,',','.') }} {{ $currency }}</td>
                    </tr>
                    <tr>
                        <td class="label">Impuestos</td>
                        <td class="value" style="text-align:right">{{ number_format($budget->tax_total,2,',','.') }} {{ $currency }}</td>
                    </tr>
                    <tr>
                        <td class="label total">TOTAL</td>
                        <td class="value total" style="text-align:right">{{ number_format($budget->total,2,',','.') }} {{ $currency }}</td>
                    </tr>
                </table>

                {{-- Datos bancarios (con flags) --}}
                @if($showBank && ($bankName || $bankHolder || $bankIban))
                <div class="mt-12">
                    <div class="mb-8"><strong>Datos bancarios</strong></div>
                    @if($bankName)<div class="mb-8">Banco: <strong>{{ $bankName }}</strong></div>@endif
                    @if($bankHolder)<div class="mb-8">Titular: <strong>{{ $bankHolder }}</strong></div>@endif
                    @if($bankIban)<div class="mb-8">Cuenta: <strong>{{ $bankIban }}</strong></div>@endif
                </div>
                @endif

                @if($billing)
                <div class="mt-12">
                    <div class="mb-8"><strong>Notas</strong></div>
                    <div class="mb-8">{!! nl2br(e($billing)) !!}</div>
                </div>
                @endif

                {{-- Botón opcional si tienes URL pública: --}}
                {{-- <p class="mt-12"><a class="kb" href="{{ $publicUrl }}" target="_blank" rel="noopener">Ver presupuesto en línea</a></p> --}}
            </div>

            <div class="foot">
                @if($settings?->address)
                {{ $settings->address }} · {{ $settings->zip }} {{ $settings->city }} · {{ $settings->country }}
                @endif
                @if($settings?->email || $settings?->phone)
                <br>
                @if($settings?->email) {{ $settings->email }} @endif
                @if($settings?->email && $settings?->phone) · @endif
                @if($settings?->phone) {{ $settings->phone }} @endif
                @endif
            </div>
        </div>
    </div>
</body>

</html>
