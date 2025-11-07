<div class="d-flex gap-2 mb-3">
  <form method="POST" action="{{ route('verifactu.web.verify', $invoice) }}">
    @csrf
    <button class="btn btn-sm btn-primary">Verificar</button>
  </form>

  @if(($invoice->verifactu_status ?? null) === 'verified')
    <form method="POST" action="{{ route('verifactu.web.annul', $invoice) }}"
          onsubmit="return confirm('¿Anular esta factura?');">
      @csrf
      <input type="hidden" name="motivo" value="Anulación desde ficha">
      <button class="btn btn-sm btn-outline-danger">Anular</button>
    </form>
  @endif
</div>
