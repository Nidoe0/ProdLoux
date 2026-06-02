<?php $__env->startSection('title', 'Connexion'); ?>

<?php $__env->startSection('content'); ?>
<div class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#FFFFFF;">
  <div class="card shadow border-0" style="max-width:700px;width:100%;border-radius:16px;overflow:hidden;">
    <div style="background:#0D6EFD;padding:2rem;text-align:center;">
      <h4 style="color:#FFFFFF; font-weight:700;margin:0;font-size:1.4rem;">
        <i class="bi bi-bag-heart-fill me-2"></i>Tsena Mora
      </h4>
      <p style="color:rgba(255,255,255,.7);margin:.4rem 0 0;font-size:.85rem;">Marketplace Malgache</p>
    </div>

    <div class="card-body p-4">
      <h5 class="fw-bold mb-1  padding:5rem;text-align:center " style="color: #0046af;font-size:2rem ">Connexion</h5>
      <p class="text-muted mb-4" style="font-size:.85rem;">Accès vendeurs et administrateurs</p>

      <?php if(session('status')): ?>
      <div class="alert alert-success py-2" style="font-size:.85rem;"><?php echo e(session('status')); ?></div>
      <?php endif; ?>

      <?php if($errors->any()): ?>
      <div class="alert alert-danger py-2" style="font-size:.85rem;">
        <?php echo e($errors->first()); ?>

      </div>
      <?php endif; ?>

      <form method="POST" action="<?php echo e(route('login')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" style="font-size:.88rem;">Adresse email</label>
          <input type="email" name="email" value="<?php echo e(old('email')); ?>"
                 class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                 placeholder="E-mail" required autofocus>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold text-dark" style="font-size:.88rem;">Mot de passe</label>
          <input type="password" name="password"
                 class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                 placeholder="Mot de passe" required>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="form-check">
            <input type="checkbox" name="remember" class="form-check-input" id="remember">
            <label class="form-check-label text-muted" for="remember" style="font-size:.82rem;">Se souvenir de moi</label>
          </div>
          <a href="<?php echo e(route('password.request')); ?>" class="text-primary" style=" font-size:.82rem;">Mot de passe oublié ?</a>
        </div>

      <button type="submit" class="btn btn-primary w-100 fw-semibold py-2">
          <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
        </button>
      </form>
    </div>

    <div class="text-center py-3 border-top" style="background:#F9FBE7;">
      <small class="text-muted">Compte acheteur ? Utilisez l'application mobile.</small>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Bureau/Laravele/resources/views/auth/login.blade.php ENDPATH**/ ?>