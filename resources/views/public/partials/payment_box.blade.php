{{-- Muestra Banco / Titular / Cuenta según flags del ajuste --}}
@php
$doc = $invoice ?? $budget ?? null;
$settings = \App\Models\UserSetting::firstWhere('user_id', $doc?->user_id);
$bankName = $settings?->bank_name;
$bankHolder = $settings?->bank_holder;
$bankIban = $settings?->bank_account;
$showBank = (isset($invoice) && ($settings?->show_bank_on_invoices ?? true))
|| (isset($budget) && ($settings?->show_bank_on_budgets ?? false));
@endphp

@if($showBank && ($bankName || $bankHolder || $bankIban))
<div class="card mb-3">
    <div class="card-header">
        <strong>Datos bancarios</strong>
    </div>
    <div class="card-body">
        <div class="row g-2">
            @if($bankName)
            <div class="col-md-4">
                <div class="text-muted small">Banco</div>
                <div>{{ $bankName }}</div>
            </div>
            @endif
            @if($bankHolder)
            <div class="col-md-4">
                <div class="text-muted small">Titular</div>
                <div>{{ $bankHolder }}</div>
            </div>
            @endif
            @if($bankIban)
            <div class="col-md-4">
                <div class="text-muted small">Cuenta</div>
                <div><code>{{ $bankIban }}</code></div>
            </div>
            @endif
        </div>
    </div>
</div>
@endif
