<?php $__env->startSection('title', 'Produits'); ?>
<?php $__env->startSection('page-title', 'Mes Produits'); ?>
<?php $__env->startSection('page-subtitle', 'Gérez les produits de votre boutique'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
    <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="text-muted small">
        <?php echo e($products->total()); ?> produit(s) au total
    </div>
    <a href="<?php echo e(route('vendor.products.create')); ?>" class="btn btn-success">
        <i class="bi bi-plus-circle me-1"></i>Ajouter un produit
    </a>
</div>

<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead style="background:#F1F8E9;">
    <tr>
        <th style="width:60px">#</th>
        <th style="width:80px">Images</th>
        <th>Nom</th>
        <th>Catégorie</th>
        <?php if(auth()->user()->isAdmin()): ?><th>Vendeur</th><?php endif; ?>
        <th>Prix</th>
        <th>Stock</th>
        <th>Avis</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
    <td class="text-muted" style="font-size:.8rem;">#<?php echo e($product->id); ?></td>
    <td>
        <?php $imgs = $product->getMedia('images'); ?>
        <?php if($imgs->count() > 0): ?>
        <div class="d-flex gap-1">
            <?php $__currentLoopData = $imgs->take(2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <img src="<?php echo e($m->getUrl('thumb')); ?>" class="rounded border" style="width:40px;height:40px;object-fit:cover;">
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if($imgs->count() > 2): ?>
            <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:.7rem;color:#666;">+<?php echo e($imgs->count()-2); ?></div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
            <i class="bi bi-image text-muted"></i>
        </div>
        <?php endif; ?>
    </td>
    <td>
        <div class="fw-semibold" style="font-size:.88rem;"><?php echo e($product->name); ?></div>
        <?php if($product->description): ?>
        <small class="text-muted"><?php echo e(Str::limit($product->description, 40)); ?></small>
        <?php endif; ?>
    </td>
    <td><span class="badge bg-secondary-subtle text-secondary border" style="font-size:.75rem;"><?php echo e($product->category->name ?? '—'); ?></span></td>
    <?php if(auth()->user()->isAdmin()): ?>
    <td style="font-size:.82rem;"><?php echo e($product->seller->shop_name ?? '—'); ?></td>
    <?php endif; ?>
    <td class="fw-semibold text-success" style="font-size:.88rem;"><?php echo e(number_format($product->price, 0, ',', ' ')); ?> Ar</td>
    <td>
        <?php $stock = $product->stock; $threshold = config('marketplace.low_stock_threshold', 5); ?>
        <span class="badge rounded-pill <?php echo e($stock > $threshold ? 'bg-success' : ($stock > 0 ? 'bg-warning text-dark' : 'bg-danger')); ?>">
            <?php echo e($stock); ?>

        </span>
        <?php if($stock <= $threshold && $stock > 0): ?>
        <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Stock bas"></i>
        <?php endif; ?>
    </td>
    <td>
        <?php $avgRating = $product->averageRating(); ?>
        <?php if($avgRating > 0): ?>
        <span style="font-size:.8rem;color:#FFB300;font-weight:600;">
            ★ <?php echo e($avgRating); ?>

        </span>
        <?php else: ?>
        <span class="text-muted" style="font-size:.75rem;">—</span>
        <?php endif; ?>
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="<?php echo e(route('vendor.products.edit', $product)); ?>" class="btn btn-sm btn-outline-primary py-0 px-2">
                <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="<?php echo e(route('vendor.products.destroy', $product)); ?>"
                  onsubmit="return confirm('Supprimer ce produit définitivement ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        <i class="bi bi-box-seam display-5 d-block mb-2 opacity-25"></i>
        Aucun produit.
        <a href="<?php echo e(route('vendor.products.create')); ?>" class="d-block mt-2 text-success">+ Créer votre premier produit</a>
    </td>
</tr>
<?php endif; ?>
</tbody>    
</table>
</div>
</div>

<div class="mt-3"></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Back/ProdLoux/resources/views/vendor/products/index.blade.php ENDPATH**/ ?>