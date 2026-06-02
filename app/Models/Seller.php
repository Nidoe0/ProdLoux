<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_name', 'description',
        'latitude', 'longitude', 'address',
        'stripe_account_id', 'stripe_onboarded',
    ];

    protected $casts = ['stripe_onboarded' => 'boolean'];

    public function user()     { return $this->belongsTo(User::class); }
    public function products() { return $this->hasMany(Product::class); }
    public function payments() { return $this->hasMany(Payment::class); }

    public function totalRevenue(): float
    {
        return (float) $this->payments()->where('status', 'transferred')->sum('seller_amount');
    }

    public function lowStockProducts()
    {
        return $this->products()->lowStock()->get();
    }
}
