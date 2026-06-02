<?php $__env->startSection('title', 'Modération des avis'); ?>
<?php $__env->startSection('page-title', 'Modération des Avis'); ?>
<?php $__env->startSection('page-subtitle', 'Gérez les avis clients et signalements'); ?>

<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?php echo e(session('success')); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>


<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="?filter=" class="btn btn-sm <?php echo e(!request('filter') ? 'btn-success' : 'btn-outline-secondary'); ?>">Tous</a>
    <a href="?filter=pending" class="btn btn-sm <?php echo e(request('filter') === 'pending' ? 'btn-warning' : 'btn-outline-warning'); ?>">En attente</a>
    <a href="?filter=flagged" class="btn btn-sm <?php echo e(request('filter') === 'flagged' ? 'btn-danger' : 'btn-outline-danger'); ?>">
        Signalés <span class="badge bg-danger ms-1">!</span>
    </a>
</div>

<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead style="background:#F1F8E9;">
    <tr>
        <th>#</th>
        <th>Client</th>
        <th>Produit</th>
        <th>Note</th>
        <th>Commentaire</th>
        <th>Statut</th>
        <th>Signalé</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $review): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr class="<?php echo e($review->flagged ? 'table-danger' : ''); ?>">
    <td class="text-muted" style="font-size:0.8rem;">#<?php echo e($review->id); ?></td>
    <td style="font-size:0.85rem;"><?php echo e($review->user?->name ?? '—'); ?></td>
    <td style="font-size:0.85rem;"><?php echo e(Str::limit($review->product?->name, 25)); ?></td>
    <td>
        <?php for($i=1; $i<=5; $i++): ?>
        <i class="bi bi-star<?php echo e($i <= $review->rating ? '-fill' : ''); ?> text-warning" style="font-size:0.75rem;"></i>
        <?php endfor; ?>
    </td>
    <td style="font-size:0.82rem;max-width:200px;"><?php echo e(Str::limit($review->body, 60)); ?></td>
    <td>
        <?php $colors=['pending'=>'warning','approved'=>'success','rejected'=>'danger']; ?>
        <span class="badge bg-<?php echo e($colors[$review->status] ?? 'secondary'); ?>"><?php echo e(ucfirst($review->status)); ?></span>
    </td>
    <td>
        <?php if($review->flagged): ?>
        <span class="badge bg-danger">🚩 <?php echo e(Str::limit($review->flag_reason, 30)); ?></span>
        <?php else: ?>
        <span class="text-muted">—</span>
        <?php endif; ?>
    </td>
    <td style="font-size:0.78rem;" class="text-muted"><?php echo e($review->created_at->format('d/m/Y')); ?></td>
    <td>
        <div class="d-flex gap-1">
            <?php if($review->status !== 'approved'): ?>
            <form method="POST" action="<?php echo e(route('admin.reviews.approve', $review)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-sm btn-success py-0 px-2" title="Approuver">✓</button>
            </form>
            <?php endif; ?>
            <?php if($review->status !== 'rejected'): ?>
            <form method="POST" action="<?php echo e(route('admin.reviews.reject', $review)); ?>">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <button class="btn btn-sm btn-warning py-0 px-2" title="Rejeter">✕</button>
            </form>
            <?php endif; ?>
            <form method="POST" action="<?php echo e(route('admin.reviews.destroy', $review)); ?>"
                  onsubmit="return confirm('Supprimer définitivement ?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button class="btn btn-sm btn-danger py-0 px-2" title="Supprimer"><i class="bi bi-trash"></i></button>
            </form>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr><td colspan="9" class="text-center text-muted py-4">Aucun avis.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<div class="mt-3"><?php echo e($reviews->links()); ?></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Bureau/Laravele/resources/views/admin/reviews/index.blade.php ENDPATH**/ ?>