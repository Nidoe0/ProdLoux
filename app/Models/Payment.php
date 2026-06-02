<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'seller_id', 'stripe_payment_intent_id',
        'stripe_transfer_id', 'amount_total', 'commission_amount',
        'seller_amount', 'commission_rate', 'status', 'stripe_response',
    ];

    protected $casts = ['stripe_response' => 'array'];

    public function order()  { return $this->belongsTo(Order::class); }
    public function seller() { return $this->belongsTo(Seller::class); }
}
