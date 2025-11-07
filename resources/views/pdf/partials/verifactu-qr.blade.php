{{-- resources/views/pdf/partials/verifactu-qr.blade.php --}}
@if(!empty($invoice->verification_qr))
    <div style="text-align:right; width:100%; margin-top:8px;">
        {!! $invoice->verification_qr !!}
        <div style="font-size:10px; margin-top:4px;">VERI*FACTU</div>
    </div>
@endif
