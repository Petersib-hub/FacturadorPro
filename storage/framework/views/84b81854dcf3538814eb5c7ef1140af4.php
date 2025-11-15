<!doctype html>
<html lang="<?php echo e(str_replace('_','-',app()->getLocale())); ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $__env->yieldContent('title','Facturador'); ?></title>
  <link rel="icon" type="image/x-icon" href="/favicon.ico">
  <link rel="manifest" href="/manifest.webmanifest">
  <meta name="theme-color" content="#2fca6c">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

  
  <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>
  <style>body{background:var(--bg-soft);}</style>
</head>
<body class="d-flex flex-column min-vh-100">

  <nav class="navbar navbar-light bg-white border-bottom">
    <div class="container-xxl">
      <a class="navbar-brand d-flex align-items-center" href="<?php echo e(url('/')); ?>">
        <img src="/logo_facturador.png" alt="Facturador" class="me-2" style="height:24px">
        <span class="fw-bold">Facturador</span>
      </a>
      <div class="ms-auto">
        <?php if(Route::has('login')): ?>
          <?php if(auth()->guard()->check()): ?>
            <a href="<?php echo e(route('dashboard')); ?>" class="btn btn-sm btn-outline-secondary">Dashboard</a>
          <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-sm btn-outline-secondary me-2">Acceder</a>
            <?php if(Route::has('register')): ?>
              <a href="<?php echo e(route('register')); ?>" class="btn btn-sm btn-brand">Crear cuenta</a>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <main class="flex-fill py-5">
    <div class="container-xxl" style="max-width: 560px;">
      
      <?php echo $__env->yieldContent('content'); ?>
    </div>
  </main>

  <footer class="mt-auto py-4 bg-white border-top">
    <div class="container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between">
      <div class="d-flex align-items-center">
        <img src="/logo_facturador.png" alt="Facturador" style="height:22px" class="me-2">
        <span class="small text-muted">© <?php echo e(date('Y')); ?> Facturador — Tu contabilidad, simplificada.</span>
      </div>
      <div class="mt-3 mt-md-0">
        <a href="#" class="text-muted small me-3">Política de privacidad</a>
        <a href="#" class="text-muted small me-3">Cookies</a>
      </div>
    </div>
  </footer>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/layouts/guest.blade.php ENDPATH**/ ?>