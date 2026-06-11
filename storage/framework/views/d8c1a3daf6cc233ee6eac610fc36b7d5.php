<?php $__env->startSection('title', 'Statistiques Admin'); ?>
<?php $__env->startSection('page-title', 'Statistiques Globales'); ?>
<?php $__env->startSection('page-subtitle', 'Vue d\'ensemble de la plateforme'); ?>

<?php $__env->startSection('content'); ?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div class="btn-group" role="group">
        <?php $__currentLoopData = ['week'=>'7 jours','month'=>'Ce mois','year'=>'Cette année']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="?period=<?php echo e($p); ?>" class="btn btn-sm <?php echo e($period === $p ? 'btn-success' : 'btn-outline-secondary'); ?>">
            <?php echo e($label); ?>

        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <form method="GET" action="<?php echo e(route('vendor.statistics.export')); ?>" class="d-flex gap-2 align-items-center">
        <input type="date" name="from" value="<?php echo e(now()->startOfMonth()->format('Y-m-d')); ?>" class="form-control form-control-sm" style="width:140px;">
        <input type="date" name="to"   value="<?php echo e(now()->format('Y-m-d')); ?>" class="form-control form-control-sm" style="width:140px;">
        <button type="submit" class="btn btn-sm btn-outline-success">
            <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
        </button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-sm-2-5 col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #2E7D32!important;">
            <div class="text-muted small mb-1">Revenus vendeurs</div>
            <div class="fw-bold text-success" style="font-size:1.3rem;"><?php echo e(number_format($data['totalRevenue'], 0, ',', ' ')); ?> Ar</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #E65100!important;">
            <div class="text-muted small mb-1">Commission plateforme</div>
            <div class="fw-bold text-warning" style="font-size:1.3rem;"><?php echo e(number_format($data['totalCommission'], 0, ',', ' ')); ?> Ar</div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3" style="border-top:4px solid #1565C0!important;">
            <div class="text-muted small mb-1">Commandes</div>
            <div class="fw-bold text-primary" style="font-size:1.3rem;"><?php echo e($data['totalOrders']); ?></div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Vendeurs</div>
            <div class="fw-bold" style="font-size:1.3rem;"><?php echo e($data['totalSellers']); ?></div>
        </div>
    </div>
    <div class="col-md" style="flex:1">
        <div class="card border-0 shadow-sm text-center p-3">
            <div class="text-muted small mb-1">Produits</div>
            <div class="fw-bold" style="font-size:1.3rem;"><?php echo e($data['totalProducts']); ?></div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                    <i class="bi bi-graph-up text-success me-1"></i>Revenus 30 derniers jours
                </h6>
                <canvas id="revenueChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="fw-semibold text-muted text-uppercase mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                    <i class="bi bi-trophy text-warning me-1"></i>Top Vendeurs
                </h6>
                <?php $__empty_1 = true; $__currentLoopData = $data['topSellers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $seller): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="d-flex align-items-center justify-content-between py-2 <?php echo e(!$loop->last ? 'border-bottom' : ''); ?>">
                    <div>
                        <div class="fw-semibold" style="font-size:.85rem;"><?php echo e($seller->shop_name); ?></div>
                        <small class="text-muted"><?php echo e($seller->user->name ?? '?'); ?></small>
                    </div>
                    <div class="text-success fw-semibold" style="font-size:.85rem;">
                        <?php echo e(number_format($seller->total_revenue ?? 0, 0, ',', ' ')); ?> Ar
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-muted text-center py-3">Aucune donnée</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
const revenueData = <?php echo json_encode($data['revenueByDay'], 15, 512) ?>;
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: Object.keys(revenueData),
        datasets: [{
            label: 'Revenus (Ar)',
            data: Object.values(revenueData),
            backgroundColor: 'rgba(46,125,50,0.75)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Back/ProdLoux/resources/views/vendor/statistics/admin.blade.php ENDPATH**/ ?>