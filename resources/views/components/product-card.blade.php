@props(['product'])
<div class="card-surface hover:shadow-lg transition-shadow group">
  <div class="w-full h-44 bg-gray-100 rounded-md mb-3 overflow-hidden flex items-center justify-center">
    @if($product->image)
      <img src="{{ $product->image }}" alt="{{ $product->name }}" class="object-contain h-full" />
    @else
      <div class="text-neutral-400">No image</div>
    @endif
  </div>

  <div class="flex items-start justify-between">
    <div>
      <h3 class="text-base font-semibold text-neutral-900">{{ $product->name }}</h3>
      <p class="text-sm text-neutral-700 mt-1">{{ Str::limit($product->description ?? '', 80) }}</p>
    </div>
    <div class="text-right">
      <div class="text-base font-bold text-primary">{{ format_price($product->price) }}</div>
      <div class="text-sm text-neutral-400">{{ $product->inventory ?? '—' }} en stock</div>
    </div>
  </div>

  <div class="mt-4 flex items-center gap-2">
    <x-button variant="primary">Ajouter</x-button>
    <a href="{{ route('products.show', $product) }}" class="text-sm text-neutral-700 underline">Voir</a>
  </div>
</div>
