<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'product_id', 'order_id',
        'rating', 'body', 'status', 'flagged', 'flag_reason',
    ];

    protected $casts = ['flagged' => 'boolean'];

    public function user()    { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function order()   { return $this->belongsTo(Order::class); }

    public function scopeApproved($query) { return $query->where('status', 'approved'); }
    public function scopePending($query)  { return $query->where('status', 'pending'); }
    public function scopeFlagged($query)  { return $query->where('flagged', true); }
}
