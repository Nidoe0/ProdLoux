<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', auth()->user()->isAdmin() ? 'Dashboard Admin' : 'Mon Tableau de Bord'); ?>
<?php $__env->startSection('page-subtitle', 'Vue d\'ensemble de votre activité'); ?>

<?php $__env->startSection('content'); ?>


<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #2E7D32 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;background:#E8F5E9;">
                    <i class="bi bi-box-seam-fill text-success" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Produits actifs</div>
                    <div class="fw-bold" style="font-size:1.8rem;line-height:1.1;color:#1B5E20;"><?php echo e($totalProducts); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #1565C0 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;background:#E3F2FD;">
                    <i class="bi bi-receipt-cutoff text-primary" style="font-size:1.4rem;"></i>
                </div>
                <div>
                    <div class="text-muted small">Commandes totales</div>
                    <div class="fw-bold" style="font-size:1.8rem;line-height:1.1;color:#1565C0;"><?php echo e($totalOrders); ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #E65100 !important;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width:52px;height:52px;background:#FFF3E0;">
                    <i class="bi bi-currency-exchange" style="font-size:1.4rem;color:#E65100;"></i>
                </div>
                <div>
                    <div class="text-muted small">Revenus confirmés</div>
                    <div class="fw-bold" style="font-size:1.5rem;line-height:1.1;color:#E65100;">
                        <?php echo e(number_format($totalRevenue, 0, ',', ' ')); ?> <small style="font-size:0.9rem;">Ar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold mb-3 text-muted text-uppercase" style="font-size:0.72rem;letter-spacing:.08em;">
                    <i class="bi bi-bar-chart-fill me-1 text-success"></i>Vue d'ensemble
                </h6>
                <canvas id="kpiChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-semibold mb-0 text-muted text-uppercase" style="font-size:0.72rem;letter-spacing:.08em;">
                        <i class="bi bi-clock-history me-1 text-primary"></i>Dernières commandes
                    </h6>
                    <a href="<?php echo e(route('vendor.orders.index')); ?>" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">
                        Tout voir →
                    </a>
                </div>

                <?php
                $statusColors = ['pending'=>'warning','confirmed'=>'success','delivered'=>'primary','cancelled'=>'danger'];
                $statusLabels = ['pending'=>'En attente','confirmed'=>'Confirmée','delivered'=>'Livrée','cancelled'=>'Annulée'];
                ?>

                <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center justify-content-between py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                    <div>
                        <span class="fw-semibold text-dark" style="font-size:0.85rem;">#<?php echo e($order->id); ?></span>
                        <span class="text-muted ms-2" style="font-size:0.82rem;"><?php echo e($order->user->name ?? '—'); ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-dark fw-semibold" style="font-size:0.82rem;">
                            <?php echo e(number_format($order->total, 0, ',', ' ')); ?> Ar
                        </span>
                        <span class="badge rounded-pill bg-<?php echo e($statusColors[$order->status] ?? 'secondary'); ?>" style="font-size:0.7rem;">
                            <?php echo e($statusLabels[$order->status] ?? $order->status); ?>

                        </span>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-inbox display-6 d-block mb-2 opacity-25"></i>
                    Aucune commande pour le moment
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="row g-3 mt-1">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span class="text-muted small fw-semibold">Actions rapides :</span>
                    <a href="<?php echo e(route('vendor.products.create')); ?>" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle me-1"></i>Ajouter un produit
                    </a>
                    <a href="<?php echo e(route('vendor.products.index')); ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-list-ul me-1"></i>Voir tous les produits
                    </a>
                    <a href="<?php echo e(route('vendor.orders.index')); ?>" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-receipt me-1"></i>Gérer les commandes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('kpiChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Produits', 'Commandes'],
            datasets: [{
                data: [<?php echo e($totalProducts); ?>, <?php echo e($totalOrders); ?>],
                backgroundColor: ['#2E7D32', '#1565C0'],
                borderWidth: 0,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 12 } } },
            }
        }
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Back/ProdLoux/resources/views/vendor/dashboard/index.blade.php ENDPATH**/ ?>