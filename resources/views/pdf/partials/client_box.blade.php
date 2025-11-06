{{-- resources/views/pdf/partials/client_box.blade.php --}}
@php
    /** Muestra datos del cliente en un bloque “Facturar a” */
    $doc = $invoice ?? $budget ?? null;
    $client = $doc?->client;
@endphp

@if($client)
<section class="client-box" style="margin:10px 0 14px 0;">
    <table class="w-100" cellspacing="0" cellpadding="0" style="border:1px solid #e5e7eb; border-radius:6px;">
        <tr>
            <td style="padding:10px 12px;">
                <div style="font-weight:700; font-size:12px; margin-bottom:6px; text-transform:uppercase; letter-spacing:.02em;">
                    {{ isset($invoice) ? 'Facturar a' : 'Cliente' }}
                </div>

                <div style="font-size:13px; line-height:1.45;">
                    <div><strong>{{ $client->name }}</strong></div>
                    @if(!empty($client->tax_id))
                        <div class="muted small">NIF/CIF: {{ $client->tax_id }}</div>
                    @endif

                    @php
                        $addr1 = trim($client->address ?? '');
                        $addr2 = trim( (($client->zip ?? '') ? $client->zip.' ' : '') . ($client->city ?? '') );
                        $country = trim($client->country ?? '');
                    @endphp
                    @if($addr1 || $addr2 || $country)
                        <div class="small">
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
</section>
@endif
