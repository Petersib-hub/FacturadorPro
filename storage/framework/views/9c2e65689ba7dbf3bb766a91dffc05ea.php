<div class="d-flex gap-2 mb-3">
  <form method="POST" action="<?php echo e(route('verifactu.web.verify', $invoice)); ?>">
    <?php echo csrf_field(); ?>
    <button class="btn btn-sm btn-primary">Verificar</button>
  </form>

  <?php if(($invoice->verifactu_status ?? null) === 'verified'): ?>
    <form method="POST" action="<?php echo e(route('verifactu.web.annul', $invoice)); ?>"
          onsubmit="return confirm('¿Anular esta factura?');">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="motivo" value="Anulación desde ficha">
      <button class="btn btn-sm btn-outline-danger">Anular</button>
    </form>
  <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/verifactu/_actions.blade.php ENDPATH**/ ?>