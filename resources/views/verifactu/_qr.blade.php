@if(!empty($invoice->verification_qr))
  <div class="border rounded p-2 mb-3" style="max-width:180px">
    {!! $invoice->verification_qr !!}
  </div>
@endif
