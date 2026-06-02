@extends('layouts.app')
@section('title', 'Éditer produit')
@section('page-title', 'Modifier le produit')
@section('page-subtitle', $product->name)

@section('content')
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card border-0 shadow-sm">
<div class="card-body p-4">
<form method="POST" action="{{ route('vendor.products.update', $product) }}" enctype="multipart/form-data">
@csrf @method('PUT')

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nom du produit *</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Catégorie *</label>
        <select name="category_id" class="form-select" required>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Prix (Ar) *</label>
        <input type="number" name="price" value="{{ old('price', $product->price) }}" class="form-control" step="0.01" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Stock *</label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" min="0" required>
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Latitude</label>
        <input type="number" name="latitude" value="{{ old('latitude', $product->latitude) }}" class="form-control" step="any">
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-12">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
    </div>
</div>

{{-- Existing images --}}
@if($mediaItems->count() > 0)
<div class="mt-3">
    <label class="form-label fw-semibold">Images actuelles</label>
    <div class="d-flex flex-wrap gap-2">
        @foreach($mediaItems as $media)
        <div class="position-relative">
            <img src="{{ $media->getUrl('thumb') }}" class="rounded border" style="width:90px;height:90px;object-fit:cover;">
            <div class="form-check position-absolute top-0 end-0 m-1">
                <input class="form-check-input" type="checkbox" name="delete_media_ids[]" value="{{ $media->id }}"
                    id="del_{{ $media->id }}" style="background:#dc3545;border-color:#dc3545;">
                <label class="form-check-label" for="del_{{ $media->id }}" style="display:none;"></label>
            </div>
            <small class="d-block text-center text-muted mt-1" style="font-size:0.65rem;">
                <label for="del_{{ $media->id }}" class="text-danger" style="cursor:pointer;">🗑 Supprimer</label>
            </small>
        </div>
        @endforeach
    </div>
    <small class="text-muted">Cochez les images à supprimer.</small>
</div>
@endif

{{-- New images --}}
<div class="mt-3">
    <label class="form-label fw-semibold">Ajouter des images <small class="text-muted">(max 5 total)</small></label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/*" id="imageInput">
    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i>Mettre à jour</button>
    <a href="{{ route('vendor.products.index') }}" class="btn btn-outline-secondary">Annuler</a>
</div>
</form>
</div>
</div>
@endsection

@section('scripts')
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
@endsection
