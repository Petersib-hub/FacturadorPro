
<?php if(session('ok') || session('warning') || session('error')): ?>
    <div class="container my-2">
        <?php if(session('ok')): ?>
            <div class="alert alert-success"><?php echo e(session('ok')); ?></div>
        <?php endif; ?>
        <?php if(session('warning')): ?>
            <div class="alert alert-warning"><?php echo e(session('warning')); ?></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div class="alert alert-danger"><?php echo e(session('error')); ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/partials/flash.blade.php ENDPATH**/ ?>