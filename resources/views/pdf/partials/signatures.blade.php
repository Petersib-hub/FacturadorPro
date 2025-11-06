{{-- resources/views/pdf/partials/signatures.blade.php --}}
@php
$doc = $invoice ?? $budget ?? null;
$client = $doc?->client;
$settings = \App\Models\UserSetting::firstWhere('user_id', $doc?->user_id);
$issuerName = $settings?->display_name ?? ($settings?->legal_name ?: 'Mi Empresa');
@endphp

<table class="w-100" cellspacing="0" cellpadding="0"
    style="width:100%; border-collapse:separate; border-spacing:0; margin-top:16px;">
    <tr>
        <td style="width:50%; padding-right:10px; vertical-align:bottom;">
            <div style="font-size:11px; color:#6c757d; margin-bottom:6px;">Firma del emisor</div>
            <div style="height:70px; border:1px dashed #cbd5e1; border-radius:8px;"></div>
            <div style="font-size:11px; color:#6c757d; margin-top:6px; text-align:center;">
                {{ $issuerName }}
            </div>
        </td>
        <td style="width:50%; padding-left:10px; vertical-align:bottom;">
            <div style="font-size:11px; color:#6c757d; margin-bottom:6px;">Firma del receptor</div>
            <div style="height:70px; border:1px dashed #cbd5e1; border-radius:8px;"></div>
            <div style="font-size:11px; color:#6c757d; margin-top:6px; text-align:center;">
                {{ $client?->name }}
            </div>
        </td>
    </tr>
</table>
