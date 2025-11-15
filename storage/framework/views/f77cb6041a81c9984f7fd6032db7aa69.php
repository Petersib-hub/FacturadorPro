<?php $__env->startSection('title','Acceder'); ?>

<?php $__env->startSection('content'); ?>
<div class="card card-soft">
  <div class="card-body p-4 p-md-5">
    <h4 class="mb-3">Acceder</h4>

    <?php if(session('status')): ?>
      <div class="alert alert-success"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('login')); ?>" class="row g-3">
      <?php echo csrf_field(); ?>
      <div class="col-12">
        <label class="form-label" for="email">Email</label>
        <input id="email" type="email" name="email" value="<?php echo e(old('email')); ?>" required autofocus
               class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div class="col-12">
        <label class="form-label" for="password">Contraseña</label>
        <input id="password" type="password" name="password" required
               class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="invalid-feedback"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>

      <div class="col-12 d-flex justify-content-between align-items-center">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" id="remember" name="remember">
          <label class="form-check-label" for="remember">Recuérdame</label>
        </div>
        <?php if(Route::has('password.request')): ?>
          <a href="<?php echo e(route('password.request')); ?>" class="small">¿Olvidaste la contraseña?</a>
        <?php endif; ?>
      </div>

      <div class="col-12">
        <button class="btn btn-brand w-100">Entrar</button>
      </div>

      <?php if(Route::has('register')): ?>
      <div class="col-12 text-center">
        <span class="text-muted small">¿No tienes cuenta?</span>
        <a href="<?php echo e(route('register')); ?>">Crear cuenta</a>
      </div>
      <?php endif; ?>
    </form>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\laravel\factura\facturadorPro\resources\views/auth/login.blade.php ENDPATH**/ ?>