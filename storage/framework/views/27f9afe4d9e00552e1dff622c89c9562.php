<?php $__env->startSection('title', 'Commandes'); ?>
<?php $__env->startSection('page-title', 'Commandes'); ?>
<?php $__env->startSection('page-subtitle', 'Suivez et gérez les commandes de vos clients'); ?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
    <i class="bi bi-check-circle me-2"></i><?php echo e(session('success')); ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php
$statusColors = ['pending'=>'warning','confirmed'=>'success','delivered'=>'primary','cancelled'=>'danger'];
$statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','delivered'=>'Livrée','cancelled'=>'Annulée'];
?>


<div class="d-flex gap-2 flex-wrap mb-3">
    <?php $__currentLoopData = $statusLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $count = $orders->where('status', $s)->count(); ?>
    <span class="badge bg-<?php echo e($statusColors[$s]); ?> bg-opacity-15 text-<?php echo e($statusColors[$s]); ?> border border-<?php echo e($statusColors[$s]); ?>-subtle px-3 py-2" style="font-size:.78rem;">
        <?php echo e($label); ?> : <?php echo e($count); ?>

    </span>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="card border-0 shadow-sm">
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead style="background:#F1F8E9;">
    <tr>
        <th>#</th>
        <th>Client</th>
        <th>Téléphone</th>
        <th>Produits</th>
        <th>Total</th>
        <th>Statut</th>
        <th>Payé via</th>
        <th>Date</th>
        <th>Changer statut</th>
    </tr>
</thead>
<tbody>
<?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<tr>
    <td class="fw-semibold text-muted" style="font-size:.82rem;">#<?php echo e($order->id); ?></td>
    <td>
        <div style="font-size:.85rem;font-weight:600;"><?php echo e($order->user->name ?? '—'); ?></div>
        <small class="text-muted"><?php echo e($order->user->email ?? ''); ?></small>
    </td>
    <td style="font-size:.82rem;"><?php echo e($order->phone ?? '—'); ?></td>
    <td style="max-width:180px;">
        <?php $__currentLoopData = $order->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <small class="d-block text-muted" style="font-size:.75rem;">
            <?php echo e(Str::limit($item->product->name ?? '?', 22)); ?> × <?php echo e($item->quantity); ?>

        </small>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </td>
    <td class="fw-bold text-success" style="font-size:.88rem;"><?php echo e(number_format($order->total, 0, ',', ' ')); ?> Ar</td>
    <td>
        <span class="badge bg-<?php echo e($statusColors[$order->status] ?? 'secondary'); ?> rounded-pill">
            <?php echo e($statusLabels[$order->status] ?? ucfirst($order->status)); ?>

        </span>
    </td>
    <td>
        <?php if($order->stripe_payment_intent_id): ?>
        <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:.7rem;">
            <i class="bi bi-credit-card me-1"></i>Stripe
        </span>
        <?php else: ?>
        <span class="text-muted" style="font-size:.75rem;">—</span>
        <?php endif; ?>
    </td>
    <td style="font-size:.78rem;" class="text-muted"><?php echo e($order->created_at->format('d/m/Y H:i')); ?></td>
    <td>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle py-0 px-2" data-bs-toggle="dropdown">
                Statut
            </button>
            <ul class="dropdown-menu shadow-sm">
                <?php $__currentLoopData = ['pending','confirmed','delivered','cancelled']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($s !== $order->status): ?>
                <li>
                    <form method="POST" action="<?php echo e(route('vendor.orders.status', [$order, $s])); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="dropdown-item d-flex align-items-center gap-2" style="font-size:.85rem;">
                            <span class="badge bg-<?php echo e($statusColors[$s]); ?>" style="width:10px;height:10px;padding:0;border-radius:50%;"></span>
                            <?php echo e($statusLabels[$s]); ?>

                        </button>
                    </form>
                </li>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    </td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        <i class="bi bi-receipt display-5 d-block mb-2 opacity-25"></i>
        Aucune commande pour le moment.
    </td>
</tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Bureau/Laravele/resources/views/vendor/orders/index.blade.php ENDPATH**/ ?>