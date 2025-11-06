{{-- resources/views/pdf/partials/totals.blade.php --}}
@php
    $doc = $invoice ?? $budget ?? null;
    $currency = $doc?->currency ?? 'EUR';
@endphp

<table class="totals-table" cellspacing="0" cellpadding="0"
       style="width:auto; margin-left:auto; border-collapse:collapse;">
    <tbody>
        <tr>
            <td class="label"
                style="text-align:right; padding:6px 8px 6px 0; white-space:nowrap;">
                Subtotal
            </td>
            <td class="value"
                style="text-align:right; padding:6px 0 6px 8px; min-width:120px;">
                {{ number_format($doc->subtotal ?? 0, 2, ',', '.') }} {{ $currency }}
            </td>
        </tr>
        <tr>
            <td class="label"
                style="text-align:right; padding:6px 8px 6px 0; white-space:nowrap;">
                Impuestos
            </td>
            <td class="value"
                style="text-align:right; padding:6px 0 6px 8px;">
                {{ number_format($doc->tax_total ?? 0, 2, ',', '.') }} {{ $currency }}
            </td>
        </tr>
        <tr class="grand-total">
            <td class="label"
                style="text-align:right; padding:8px 8px 6px 0; font-weight:700; white-space:nowrap;">
                TOTAL
            </td>
            <td class="value"
                style="text-align:right; padding:8px 0 6px 8px; font-weight:700;">
                {{ number_format($doc->total ?? 0, 2, ',', '.') }} {{ $currency }}
            </td>
        </tr>

        @if(isset($invoice))
            <tr>
                <td class="label"
                    style="text-align:right; padding:6px 8px 6px 0; white-space:nowrap;">
                    Pagado
                </td>
                <td class="value"
                    style="text-align:right; padding:6px 0 6px 8px;">
                    {{ number_format($invoice->amount_paid ?? 0, 2, ',', '.') }} {{ $currency }}
                </td>
            </tr>
            <tr>
                <td class="label"
                    style="text-align:right; padding:6px 8px 0 0; white-space:nowrap;">
                    Pendiente
                </td>
                <td class="value"
                    style="text-align:right; padding:6px 0 0 8px;">
                    {{ number_format(($invoice->total ?? 0) - ($invoice->amount_paid ?? 0), 2, ',', '.') }} {{ $currency }}
                </td>
            </tr>
        @endif
    </tbody>
</table>
