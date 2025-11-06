{{-- resources/views/pdf/partials/header.blade.php --}}
@php
    $doc = $invoice ?? $budget ?? null;
    $s = \App\Models\UserSetting::firstWhere('user_id', $doc->user_id ?? null);

    $issuerName  = $s?->display_name ?? ($s?->legal_name ?: 'Mi Empresa');
    $issuerTax   = $s?->tax_id;
    $issuerAdr1  = $s?->address;
    $issuerAdr2  = trim(($s?->zip ? $s->zip.' ' : '').($s?->city ?? ''));
    $issuerCtry  = $s?->country;

    $issuerEmail = $s?->email ?: optional($s?->user)->email;  // fallback al email del usuario
    $issuerPhone = $s?->phone;

    $logoPath = \App\Support\Branding::tenantLogoDiskPathForPdf($s?->user_id);
@endphp

<header>
    <table class="w-100">
        <tr>
            <td style="width:60%; vertical-align:top;">
                <div class="grid">
                    <div class="col col-7">
                        @if(!empty($logoPath))
                            <img class="logo" src="{{ $logoPath }}" alt="Logo">
                        @endif
                        <div style="font-size:16px; font-weight:700; margin-top:6px;">
                            {{ $issuerName }}
                        </div>
                        @if($issuerTax)
                            <div class="muted small">NIF/CIF: {{ $issuerTax }}</div>
                        @endif
                        @if($issuerAdr1 || $issuerAdr2 || $issuerCtry)
                            <div class="small muted">
                                @if($issuerAdr1) {{ $issuerAdr1 }}<br>@endif
                                @if($issuerAdr2) {{ $issuerAdr2 }}<br>@endif
                                @if($issuerCtry) {{ $issuerCtry }}<br>@endif
                            </div>
                        @endif
                        @if($issuerEmail || $issuerPhone)
                            <div class="small muted">
                                @if($issuerEmail) {{ $issuerEmail }} @endif
                                @if($issuerEmail && $issuerPhone) · @endif
                                @if($issuerPhone) {{ $issuerPhone }} @endif
                            </div>
                        @endif
                    </div>
                    <div class="col col-5"></div>
                </div>
            </td>
            <td style="width:40%; text-align:right; vertical-align:top;">
                <div style="font-size:22px; font-weight:800;">
                    {{ isset($invoice) ? 'FACTURA' : 'PRESUPUESTO' }}
                </div>
                <div class="muted">{{ isset($invoice) ? $invoice->number : ($budget->number ?? '') }}</div>
                <div class="badge" style="margin-top:4px;">
                    {{ ucfirst(isset($invoice) ? $invoice->status : ($budget->status ?? '')) }}
                </div>
                <table class="w-100" style="margin-top:10px;">
                    <tr>
                        <td class="muted small">Fecha</td>
                        <td class="text-right">{{ optional(isset($invoice) ? $invoice->date : ($budget->date ?? null))->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="muted small">{{ isset($invoice)?'Vence':'Válido hasta' }}</td>
                        <td class="text-right">{{ optional(isset($invoice) ? $invoice->due_date : ($budget->due_date ?? null))->format('d/m/Y') ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</header>
