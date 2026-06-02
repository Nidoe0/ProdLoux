@extends('layouts.app')
@section('title', 'Nouveau produit')
@section('page-title', 'Ajouter un produit')
@section('page-subtitle', 'Créez un nouveau produit pour votre boutique')

@section('content')
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="card border-0 shadow-sm">
<div class="card-body p-4">
<form method="POST" action="{{ route('vendor.products.store') }}" enctype="multipart/form-data">
@csrf

@if(auth()->user()->isAdmin())
<div class="mb-3">
    <label class="form-label fw-semibold">Vendeur <span class="text-danger">*</span></label>
    <select name="seller_id" class="form-select @error('seller_id') is-invalid @enderror" required>
        <option value="">— Sélectionnez un vendeur —</option>
        @foreach($sellers as $s)
        <option value="{{ $s->id }}" {{ old('seller_id') == $s->id ? 'selected' : '' }}>
            {{ $s->shop_name }} ({{ $s->user->name ?? '?' }})
        </option>
        @endforeach
    </select>
    @error('seller_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
@endif

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label fw-semibold">Nom du produit *</label>
        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required placeholder="ex: Vanille de Madagascar">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Catégorie *</label>
        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
            <option value="">— Catégorie —</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-4">
        <label class="form-label fw-semibold">Prix (Ar) *</label>
        <input type="number" name="price" value="{{ old('price') }}" class="form-control @error('price') is-invalid @enderror" step="0.01" min="0" required>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Stock *</label>
        <input type="number" name="stock" value="{{ old('stock', 0) }}" class="form-control @error('stock') is-invalid @enderror" min="0" required>
        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-semibold">Description</label>
        <textarea name="description" class="form-control" rows="1" placeholder="Description courte...">{{ old('description') }}</textarea>
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Latitude GPS</label>
        <input type="number" name="latitude" value="{{ old('latitude') }}" class="form-control" step="any" placeholder="-18.91">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-semibold">Longitude GPS</label>
        <input type="number" name="longitude" value="{{ old('longitude') }}" class="form-control" step="any" placeholder="47.53">
    </div>
</div>

{{-- Multiple images --}}
<div class="mt-3">
    <label class="form-label fw-semibold">Images du produit <small class="text-muted">(max 5 — JPEG/PNG/WebP)</small></label>
    <input type="file" name="images[]" class="form-control @error('images.*') is-invalid @enderror" multiple accept="image/*" id="imageInput">
    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle me-1"></i>Enregistrer</button>
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
            const div = document.createElement('div');
            div.className = 'position-relative';
            div.innerHTML = `<img src="${e.target.result}" class="rounded border" style="width:90px;height:90px;object-fit:cover;">`;
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
});
</script>
@endsection
