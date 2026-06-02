<?php $__env->startSection('content'); ?>
<div class="d-flex align-items-center justify-content-center" style="min-height:85vh; background: linear-gradient(180deg,#eef2ff 0%, #f8fafc 100%);">
  <div class="container">
    <div class="row align-items-center justify-content-between gy-4">
      <div class="col-lg-6">
        <div class="p-5 rounded-4 shadow-sm" style="background:rgba(255,255,255,0.92); backdrop-filter: blur(16px);">
          <span class="badge bg-primary text-uppercase fw-semibold mb-3" style="font-size:1rem; padding:0.75rem 1.25rem; display:inline-block;">Accueil administrateur</span>
          <h1 class="display-5 fw-bold mb-3" style="color:#ffb703;">Bienvenue</h1>
          <p class="lead text-secondary">Voici votre page d'accueil. Cliquez sur « Se connecter » pour accéder au tableau de bord sécurisé.</p>
          <p class="text-muted">Un seul compte administrateur est configuré : utilisez votre nom, adresse email et mot de passe.</p>
          <div class="mt-4">
            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-lg px-5">Se connecter</a>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="rounded-4 overflow-hidden shadow" style="min-height:420px; background-image:url('<?php echo e(asset('images/madagascar-product.svg')); ?>'); background-size:cover; background-position:center;">
          <div class="h-100 w-100" style="background:linear-gradient(180deg, rgba(13, 110, 253, 0.18), rgba(13, 110, 253, 0.4));"></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Bureau/Laravele/resources/views/admin/home.blade.php ENDPATH**/ ?>