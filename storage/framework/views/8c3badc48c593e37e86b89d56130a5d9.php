
<?php
    use Illuminate\Support\Str;
    // Fuente de estado: explícito ($status) o desde $invoice
    $status = $status ?? ($invoice->verifactu_status ?? null);
?>

<?php if($status): ?>
    <?php
        $class = match($status) {
            'verified' => 'bg-success',
            'pending'  => 'bg-warning text-dark',
            'failed'   => 'bg-danger',
            'annulled' => 'bg-secondary',
            default    => 'bg-secondary',
        };
    ?>
    <span class="badge <?php echo e($class); ?>">Veri*factu: <?php echo e(ucfirst($status)); ?></span>

    
    <?php if(!empty($invoice) && !empty($invoice->verification_hash)): ?>
        <small class="text-muted ms-2">Hash: <?php echo e(Str::limit($invoice->verification_hash, 18)); ?></small>
    <?php endif; ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/verifactu/status-badge.blade.php ENDPATH**/ ?>