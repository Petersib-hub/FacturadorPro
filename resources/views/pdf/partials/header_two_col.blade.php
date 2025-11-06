{{-- resources/views/pdf/partials/header_two_col.blade.php --}}
@php
    // Documento y ajustes del emisor
    $doc = $invoice ?? $budget ?? null;
    $s = \App\Models\UserSetting::firstWhere('user_id', $doc?->user_id);
    $issuerName = $s?->display_name ?? ($s?->legal_name ?: 'Mi Empresa');
    $issuerTax  = $s?->tax_id;
    $issuerAdr1 = $s?->address;
    $issuerAdr2 = trim(($s?->zip ? $s->zip.' ' : '').($s?->city ?? ''));
    $issuerCtry = $s?->country;
    $logoPath   = \App\Support\Branding::tenantLogoDiskPathForPdf($s?->user_id);

    // Cliente
    $client = $doc?->client;

    // Etiquetas
    $isInvoice = isset($invoice);
    $title     = $isInvoice ? 'FACTURA' : 'PRESUPUESTO';
    $number    = $isInvoice ? ($invoice->number ?? '') : ($budget->number ?? '');
    $date      = optional($isInvoice ? ($invoice->date ?? null) : ($budget->date ?? null))->format('d/m/Y');
    $due       = optional($isInvoice ? ($invoice->due_date ?? null) : ($budget->due_date ?? null))->format('d/m/Y');
    $status    = ucfirst($isInvoice ? ($invoice->status ?? '') : ($budget->status ?? ''));
    $dueLabel  = $isInvoice ? 'Vence' : 'Válido hasta';
@endphp

<header style="margin-bottom:14px; padding-bottom:8px; border-bottom:2px solid #e5e7eb;">
    <table class="w-100" cellspacing="0" cellpadding="0" style="width:100%;">
        <tr>
            {{-- Columna izquierda: emisor --}}
            <td style="width:60%; vertical-align:top;">
                <div style="display:flex; gap:12px; align-items:flex-start;">
                    @if(!empty($logoPath))
                        <img class="logo" src="{{ $logoPath }}" alt="Logo" style="max-height:75px;">
                    @endif
                    <div>
                        <div style="font-size:16px; font-weight:800; margin-bottom:4px;">{{ $issuerName }}</div>
                        @if($issuerTax)
                            <div class="small muted">NIF/CIF: {{ $issuerTax }}</div>
                        @endif
                        @if($issuerAdr1)
                            <div class="small muted">{{ $issuerAdr1 }}</div>
                        @endif
                        @if($issuerAdr2 || $issuerCtry)
                            <div class="small muted">{{ $issuerAdr2 }} {{ $issuerCtry }}</div>
                        @endif
                    </div>
                </div>
            </td>

            {{-- Columna derecha: título + número + fechas --}}
            <td style="width:40%; vertical-align:top; text-align:right;">
                <div style="font-size:22px; font-weight:800;">{{ $title }}</div>
                <div class="muted" style="margin-bottom:4px;">{{ $number }}</div>
                @if($status)
                    <span class="badge" style="display:inline-block; padding:2px 10px; border:1px solid #111; border-radius:9999px; font-size:11px;">
                        {{ $status }}
                    </span>
                @endif

                <table class="w-100" cellspacing="0" cellpadding="0" style="width:100%; margin-top:8px;">
                    <tr>
                        <td class="muted small" style="text-align:left;">Fecha</td>
                        <td class="text-right" style="text-align:right;">{{ $date ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="muted small" style="text-align:left;">{{ $dueLabel }}</td>
                        <td class="text-right" style="text-align:right;">{{ $due ?: '—' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Bloque cliente debajo, ancho completo --}}
    @if($client)
        <div style="margin-top:12px;">
            <table class="w-100" cellspacing="0" cellpadding="0" style="width:100%; border:1px solid #e5e7eb; border-radius:8px;">
                <tr>
                    <td style="padding:10px 12px;">
                        <div style="font-weight:700; font-size:12px; margin-bottom:6px; text-transform:uppercase; letter-spacing:.02em;">
                            {{ $isInvoice ? 'Facturar a' : 'Cliente' }}
                        </div>
                        <div style="font-size:13px; line-height:1.4;">
                            <div><strong>{{ $client->name }}</strong></div>
                            @if(!empty($client->tax_id))
                                <div class="small muted">NIF/CIF: {{ $client->tax_id }}</div>
                            @endif

                            @php
                                $addr1 = trim($client->address ?? '');
                                $addr2 = trim( (($client->zip ?? '') ? $client->zip.' ' : '') . ($client->city ?? '') );
                                $country = trim($client->country ?? '');
                            @endphp

                            @if($addr1 || $addr2 || $country)
                                <div class="small muted">
                                    {{ $addr1 }}<br>
                                    {{ $addr2 }} {{ $country }}
                                </div>
                            @endif

                            @if(!empty($client->phone) || !empty($client->email))
                                <div class="small muted" style="margin-top:6px;">
                                    @if(!empty($client->phone)) Tel: {{ $client->phone }} @endif
                                    @if(!empty($client->phone) && !empty($client->email)) · @endif
                                    @if(!empty($client->email)) {{ $client->email }} @endif
                                </div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif
</header>
