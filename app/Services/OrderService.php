<?php

namespace App\Services;

use App\Mail\OrderConfirmationMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OrderService
{
    /**
     * Create an order from the buyer's cart.
     * Checks stock, decrements, notifies, sends email — all in one DB transaction.
     */
    public function createFromCart(int $userId, array $meta = []): Order
    {
        return DB::transaction(function () use ($userId, $meta) {

            // 1. Load cart with locked products (prevents race conditions)
            $cartItems = Cart::with('product')->where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => ['Votre panier est vide.'],
                ]);
            }

            // 2. Stock check — before touching anything
            foreach ($cartItems as $item) {
                if (! $item->product) {
                    throw ValidationException::withMessages([
                        'stock' => ['Un produit de votre panier n\'existe plus.'],
                    ]);
                }
                if ($item->product->stock < $item->quantity) {
                    throw ValidationException::withMessages([
                        'stock' => [
                            "Stock insuffisant pour « {$item->product->name} » "
                            . "(demandé: {$item->quantity}, disponible: {$item->product->stock})."
                        ],
                    ]);
                }
            }

            // 3. Calculate total
            $total = $cartItems->sum(fn ($i) => $i->product->price * $i->quantity);

            // 4. Create order
            $order = Order::create([
                'user_id'          => $userId,
                'total'            => $total,
                'status'           => 'pending',
                'delivery_address' => $meta['delivery_address'] ?? null,
                'phone'            => $meta['phone'] ?? null,
            ]);

            // 5. Create order items + decrement stock
            $sellerUsers = collect();

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'price'      => $item->product->price,
                ]);

                $item->product->decrement('stock', $item->quantity);

                // Refresh to get updated stock value
                $freshProduct = $item->product->fresh();

                // Low-stock notification to seller
                if ($freshProduct && $freshProduct->is_low_stock) {
                    $seller = $freshProduct->seller;
                    if ($seller?->user) {
                        $seller->user->notify(new LowStockNotification($freshProduct));
                    }
                }

                // Collect seller user for new-order notification (deduplicated)
                $seller = $item->product->seller;
                if ($seller?->user && ! $sellerUsers->contains('id', $seller->user->id)) {
                    $sellerUsers->push($seller->user);
                }
            }

            // 6. Notify each unique seller about the new order
            foreach ($sellerUsers as $sellerUser) {
                $sellerUser->notify(new NewOrderNotification($order));
            }

            // 7. Clear cart
            Cart::where('user_id', $userId)->delete();

            // 8. Send confirmation email to buyer (outside transaction is fine, but inside is safer)
            $user = \App\Models\User::find($userId);
            if ($user) {
                $orderWithItems = $order->load('items.product');
                Mail::to($user->email)->send(new OrderConfirmationMail($orderWithItems));
            }

            return $order->load('items.product.seller');
        });
    }

    /**
     * Create payment split records after a successful Stripe payment.
     * Must be called AFTER the order is confirmed.
     */
    public function createPayments(Order $order, string $paymentIntentId, int $commissionRate = 10): void
    {
        // Always reload items with products to avoid empty collection bugs
        $order->load('items.product');

        $order->update([
            'stripe_payment_intent_id' => $paymentIntentId,
            'status'                   => 'confirmed',
            'confirmed_at'             => now(),
        ]);

        // Group items by seller_id
        $bySeller = $order->items->groupBy(fn ($i) => $i->product?->seller_id);

        foreach ($bySeller as $sellerId => $items) {
            if (! $sellerId) continue; // skip if product was deleted

            $sellerTotal   = $items->sum(fn ($i) => $i->price * $i->quantity);
            $commissionAmt = round($sellerTotal * $commissionRate / 100, 2);
            $sellerAmt     = round($sellerTotal - $commissionAmt, 2);

            Payment::create([
                'order_id'                 => $order->id,
                'seller_id'                => $sellerId,
                'stripe_payment_intent_id' => $paymentIntentId,
                'amount_total'             => $sellerTotal,
                'commission_amount'        => $commissionAmt,
                'seller_amount'            => $sellerAmt,
                'commission_rate'          => $commissionRate,
                'status'                   => 'paid',
            ]);
        }
    }
}
