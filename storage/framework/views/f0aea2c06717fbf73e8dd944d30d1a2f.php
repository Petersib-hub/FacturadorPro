<?php $__env->startSection('title','Bienvenido'); ?>
<?php $__env->startSection('content'); ?>
<div class="p-5 p-md-6 bg-gradient-brand text-white rounded-4 card-soft">
    <div class="row align-items-center">
        <div class="col-lg-7">
            <h1 class="display-5 fw-bold mb-3">Facturador</h1>
            <p class="lead mb-4">Tu contabilidad, simplificada. Multi-tenant, segura, y lista para crecer a móvil y escritorio.</p>
            <a href="<?php echo e(route('register')); ?>" class="btn btn-light me-2">Crear mi cuenta</a>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-outline-light">Ya tengo cuenta</a>
        </div>
        <div class="col-lg-5 text-center d-none d-lg-block">
            <img src="/logo_facturador_blanco.png" alt="Logo" style="height:120px;opacity:.9">
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/welcome.blade.php ENDPATH**/ ?>