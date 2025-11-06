{{-- resources/views/pdf/partials/notes.blade.php --}}
@php
$doc = $invoice ?? $budget ?? null;
$settings = \App\Models\UserSetting::firstWhere('user_id', $doc?->user_id);

$bankName = $settings?->bank_name;
$bankHolder = $settings?->bank_holder;
$bankIban = $settings?->bank_account;
$billing = $settings?->billing_notes;
$notes = $doc?->notes;
$terms = $doc?->terms;

$showBank =
(isset($invoice) && ($settings?->show_bank_on_invoices ?? true))
|| (isset($budget) && ($settings?->show_bank_on_budgets ?? false));
@endphp

@if(($showBank && ($bankName || $bankHolder || $bankIban)) || $billing || $notes || $terms)
<section class="notes" style="margin-top:14px;">
    @if($showBank && ($bankName || $bankHolder || $bankIban))
    <div class="note-block" style="margin-top:10px;">
        <div class="note-title" style="font-weight:700; margin-bottom:4px;">Datos bancarios</div>
        <div class="note-text">
            @if($bankName)<div><strong>Banco:</strong> {{ $bankName }}</div>@endif
            @if($bankHolder)<div><strong>Titular:</strong> {{ $bankHolder }}</div>@endif
            @if($bankIban)<div><strong>Cuenta:</strong> {{ $bankIban }}</div>@endif
        </div>
    </div>
    @endif

    @if($billing)
    <div class="note-block" style="margin-top:10px;">
        <div class="note-title" style="font-weight:700; margin-bottom:4px;">Notas de pago</div>
        <div class="note-text">{!! nl2br(e($billing)) !!}</div>
    </div>
    @endif

    @if($notes)
    <div class="note-block" style="margin-top:10px;">
        <div class="note-title" style="font-weight:700; margin-bottom:4px;">Notas</div>
        <div class="note-text">{!! nl2br(e($notes)) !!}</div>
    </div>
    @endif

    @if($terms)
    <div class="note-block" style="margin-top:10px;">
        <div class="note-title" style="font-weight:700; margin-bottom:4px;">Términos y condiciones</div>
        <div class="note-text">{!! nl2br(e($terms)) !!}</div>
    </div>
    @endif
</section>
@endif
