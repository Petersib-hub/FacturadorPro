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

    <?php echo $__env->yieldPushContent('head'); ?>
    <style>
        body {
            background: var(--bg-soft);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container-xxl">
            <a class="navbar-brand d-flex align-items-center" href="<?php echo e(url('/')); ?>">
                <img src="<?php echo e(\App\Support\Branding::appLogoUrl()); ?>" alt="App" style="height:28px">
                <span class="fw-bold ms-2">Facturador</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if(auth()->guard()->check()): ?>
                    <?php if(Route::has('dashboard')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>"
                            href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                    </li>
                    <?php endif; ?>

                    <?php if(Route::has('clients.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('clients.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('clients.index')); ?>">Clientes</a>
                    </li>
                    <?php endif; ?>

                    <?php if(Route::has('products.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('products.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('products.index')); ?>">Productos</a>
                    </li>
                    <?php endif; ?>

                    <?php if(Route::has('budgets.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('budgets.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('budgets.index')); ?>">Presupuestos</a>
                    </li>
                    <?php endif; ?>

                    <?php if(Route::has('invoices.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('invoices.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('invoices.index')); ?>">Facturas</a>
                    </li>
                    <?php endif; ?>

                    
                    <?php if(Route::has('recurring-invoices.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('recurring-invoices.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('recurring-invoices.index')); ?>">Recurrentes</a>
                    </li>
                    <?php endif; ?>

                    <?php if(Route::has('tax-rates.index')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo e(request()->routeIs('tax-rates.*') ? 'active' : ''); ?>"
                            href="<?php echo e(route('tax-rates.index')); ?>">Impuestos</a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <?php echo e(auth()->user()->name); ?>

                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if(Route::has('settings.edit')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('settings.edit')); ?>">Ajustes del negocio</a></li>
                            <?php endif; ?>
                            <?php if(Route::has('profile.edit')): ?>
                            <li><a class="dropdown-item" href="<?php echo e(route('profile.edit')); ?>">Perfil</a></li>
                            <?php endif; ?>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="<?php echo e(route('logout')); ?>"> <?php echo csrf_field(); ?>
                                    <button class="dropdown-item">Salir</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>

                    <?php if(auth()->guard()->guest()): ?>
                    <?php if(Route::has('login')): ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-brand ms-lg-3" href="<?php echo e(route('login')); ?>">Acceder</a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main class="flex-fill py-4">
        <div class="container-xxl">
            <?php if(session('ok')): ?> <div class="alert alert-success"><?php echo e(session('ok')); ?></div> <?php endif; ?>
            <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <strong>Revisa los errores:</strong>
                <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
            </div>
            <?php endif; ?>
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </main>

    <?php echo $__env->make('partials.flash', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    

    <footer class="mt-auto py-4 bg-white border-top">
        <div class="container-xxl d-flex flex-column flex-md-row align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img src="/logo_facturador.png" alt="Facturador" style="height:22px" class="me-2">
                <span class="small text-muted">© <?php echo e(date('Y')); ?> Facturador — Tu contabilidad, simplificada.</span>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="#" class="text-muted small me-3">Política de privacidad</a>
                <a href="#" class="text-muted small me-3">Cookies</a>
                <button id="btnInstall" class="btn btn-sm btn-outline-success d-none">Instalar app</button>
            </div>
        </div>
    </footer>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/layouts/app.blade.php ENDPATH**/ ?>