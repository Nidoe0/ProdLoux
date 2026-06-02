<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'seller_id', 'category_id', 'name', 'description',
        'price', 'stock', 'latitude', 'longitude',
    ];

    // NOTE: We do NOT put image accessors in $appends to avoid N+1.
    // Call ->images_urls and ->first_image_url explicitly where needed.

    /* ── Relationships ───────────────────────────────────────────── */

    public function seller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /* ── Spatie Media ────────────────────────────────────────────── */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
             ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
             ->withResponsiveImages();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
             ->width(200)
             ->height(200)
             ->nonQueued();

        $this->addMediaConversion('medium')
             ->width(600)
             ->height(600)
             ->nonQueued();
    }

    /* ── Accessors (not in $appends — must be called explicitly) ─── */

    public function getFirstImageUrlAttribute(): string
    {
        $media = $this->getFirstMedia('images');
        return $media ? $media->getUrl('thumb') : asset('images/placeholder.png');
    }

    public function getImagesUrlsAttribute(): array
    {
        return $this->getMedia('images')->map(fn ($m) => [
            'id'     => $m->id,
            'url'    => $m->getUrl(),
            'thumb'  => $m->getUrl('thumb'),
            'medium' => $m->getUrl('medium'),
        ])->toArray();
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= (int) config('marketplace.low_stock_threshold', 5);
    }

    /* ── Helpers ─────────────────────────────────────────────────── */

    public function averageRating(): float
    {
        $avg = $this->reviews()->where('status', 'approved')->avg('rating');
        return round((float) ($avg ?? 0), 1);
    }

    /* ── Scopes ──────────────────────────────────────────────────── */

    public function scopeLowStock($query)
    {
        return $query->where('stock', '<=', config('marketplace.low_stock_threshold', 5));
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }
}
