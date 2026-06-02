@extends('layouts.app')
@section('title', 'Produits')
@section('page-title', 'Mes Produits')
@section('page-subtitle', 'Gérez les produits de votre boutique')

@section('content')

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="d-flex align-items-center justify-content-between mb-3">
    <div class="text-muted small">
        {{ $products->total() }} produit(s) au total
    </div>
    <a href="{{ route('vendor.products.create') }}" class="btn btn-success">
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
        @if(auth()->user()->isAdmin())<th>Vendeur</th>@endif
        <th>Prix</th>
        <th>Stock</th>
        <th>Avis</th>
        <th>Actions</th>
    </tr>
</thead>
<tbody>
@forelse($products as $product)
<tr>
    <td class="text-muted" style="font-size:.8rem;">#{{ $product->id }}</td>
    <td>
        @php $imgs = $product->getMedia('images'); @endphp
        @if($imgs->count() > 0)
        <div class="d-flex gap-1">
            @foreach($imgs->take(2) as $m)
            <img src="{{ $m->getUrl('thumb') }}" class="rounded border" style="width:40px;height:40px;object-fit:cover;">
            @endforeach
            @if($imgs->count() > 2)
            <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;font-size:.7rem;color:#666;">+{{ $imgs->count()-2 }}</div>
            @endif
        </div>
        @else
        <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
            <i class="bi bi-image text-muted"></i>
        </div>
        @endif
    </td>
    <td>
        <div class="fw-semibold" style="font-size:.88rem;">{{ $product->name }}</div>
        @if($product->description)
        <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
        @endif
    </td>
    <td><span class="badge bg-secondary-subtle text-secondary border" style="font-size:.75rem;">{{ $product->category->name ?? '—' }}</span></td>
    @if(auth()->user()->isAdmin())
    <td style="font-size:.82rem;">{{ $product->seller->shop_name ?? '—' }}</td>
    @endif
    <td class="fw-semibold text-success" style="font-size:.88rem;">{{ number_format($product->price, 0, ',', ' ') }} Ar</td>
    <td>
        @php $stock = $product->stock; $threshold = config('marketplace.low_stock_threshold', 5); @endphp
        <span class="badge rounded-pill {{ $stock > $threshold ? 'bg-success' : ($stock > 0 ? 'bg-warning text-dark' : 'bg-danger') }}">
            {{ $stock }}
        </span>
        @if($stock <= $threshold && $stock > 0)
        <i class="bi bi-exclamation-triangle-fill text-warning ms-1" title="Stock bas"></i>
        @endif
    </td>
    <td>
        @php $avgRating = $product->averageRating(); @endphp
        @if($avgRating > 0)
        <span style="font-size:.8rem;color:#FFB300;font-weight:600;">
            ★ {{ $avgRating }}
        </span>
        @else
        <span class="text-muted" style="font-size:.75rem;">—</span>
        @endif
    </td>
    <td>
        <div class="d-flex gap-1">
            <a href="{{ route('vendor.products.edit', $product) }}" class="btn btn-sm btn-outline-primary py-0 px-2">
                <i class="bi bi-pencil"></i>
            </a>
            <form method="POST" action="{{ route('vendor.products.destroy', $product) }}"
                  onsubmit="return confirm('Supprimer ce produit définitivement ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center py-5 text-muted">
        <i class="bi bi-box-seam display-5 d-block mb-2 opacity-25"></i>
        Aucun produit.
        <a href="{{ route('vendor.products.create') }}" class="d-block mt-2 text-success">+ Créer votre premier produit</a>
    </td>
</tr>
@endforelse
</tbody>    
</table>
</div>
</div>

<div class="mt-3"></div>
@endsection
