<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'total', 'status',
        'stripe_payment_intent_id', 'delivery_address', 'phone', 'confirmed_at',
    ];

    protected $casts = ['confirmed_at' => 'datetime'];

    public function user()     { return $this->belongsTo(User::class); }
    public function items()    { return $this->hasMany(OrderItem::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function reviews()  { return $this->hasMany(Review::class); }

    public function scopeConfirmed($q) { return $q->where('status', 'confirmed'); }
    public function scopeByPeriod($q, $from, $to) {
        return $q->whereBetween('created_at', [$from, $to]);
    }
}
