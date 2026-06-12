<?php $__env->startSection('title', 'Éditer produit'); ?>
<?php $__env->startSection('page-title', 'Modifier le produit'); ?>
<?php $__env->startSection('page-subtitle', $product->name); ?>

<?php $__env->startSection('content'); ?>
<?php if($errors->any()): ?>
<div class="alert alert-danger">
    <ul class="mb-0"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
<div class="card-body p-4">
<form method="POST" action="<?php echo e(route('vendor.products.update', $product)); ?>" enctype="multipart/form-data">
<?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nom du produit *</label>
        <input type="text" name="name" value="<?php echo e(old('name', $product->name)); ?>" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Catégorie *</label>
        <select name="category_id" class="form-select" required>
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cat->id); ?>" <?php echo e($product->category_id == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Prix (Ar) *</label>
        <input type="number" name="price" value="<?php echo e(old('price', $product->price)); ?>" class="form-control" step="0.01" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Stock *</label>
        <input type="number" name="stock" value="<?php echo e(old('stock', $product->stock)); ?>" class="form-control" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Latitude</label>
        <input type="number" name="latitude" value="<?php echo e(old('latitude', $product->latitude)); ?>" class="form-control" step="any">
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="3"><?php echo e(old('description', $product->description)); ?></textarea>
    </div>
</div>


<?php if($mediaItems->count() > 0): ?>
<div class="mt-3">
    <label class="form-label fw-semibold">Images actuelles</label>
    <div class="d-flex flex-wrap gap-2">
        <?php $__currentLoopData = $mediaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $media): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="position-relative">
            <img src="<?php echo e($media->getUrl('thumb')); ?>" class="rounded border" style="width:90px;height:90px;object-fit:cover;">
            <div class="form-check position-absolute top-0 end-0 m-1">
                <input class="form-check-input" type="checkbox" name="delete_media_ids[]" value="<?php echo e($media->id); ?>"
                    id="del_<?php echo e($media->id); ?>" style="background:#dc3545;border-color:#dc3545;">
                <label class="form-check-label" for="del_<?php echo e($media->id); ?>" style="display:none;"></label>
            </div>
            <small class="d-block text-center text-muted mt-1" style="font-size:0.65rem;">
                <label for="del_<?php echo e($media->id); ?>" class="text-danger" style="cursor:pointer;">🗑 Supprimer</label>
            </small>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <small class="text-muted">Cochez les images à supprimer.</small>
</div>
<?php endif; ?>


<div class="mt-3">
    <label class="form-label fw-semibold">Ajouter des images <small class="text-muted">(max 5 total)</small></label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/*" id="imageInput">
    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i>Mettre à jour</button>
    <a href="<?php echo e(route('vendor.products.index')); ?>" class="btn btn-outline-secondary">Annuler</a>
</div>
</form>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.getElementById('imageInput').addEventListener('change', function() {
    const preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    [...this.files].forEach(file => {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML += `<img src="${e.target.result}" class="rounded border" style="width:90px;height:90px;object-fit:cover;">`;
        };
        reader.readAsDataURL(file);
    });
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/kaly/Back/ProdLoux/resources/views/vendor/products/edit.blade.php ENDPATH**/ ?>