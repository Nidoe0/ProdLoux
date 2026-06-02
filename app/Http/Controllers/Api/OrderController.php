<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\StripeService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private StripeService $stripeService
    ) {}

    /**
     * POST /api/orders
     * Creates order from cart, checks stock, decrements, sends email.
     */
    public function store(Request $request)
    {
        $request->validate([
            'delivery_address' => 'nullable|string|max:500',
            'phone'            => 'nullable|string|max:20',
        ]);

        $order = $this->orderService->createFromCart($request->user()->id, $request->only('delivery_address', 'phone'));

        return response()->json([
            'message' => 'Commande créée avec succès.',
            'order'   => $order,
        ], 201);
    }

    /**
     * POST /api/orders/{order}/payment-intent
     * Create Stripe PaymentIntent for an order.
     */
    public function createPaymentIntent(Request $request, \App\Models\Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);
        abort_if($order->status !== 'pending', 422, 'Cette commande a déjà été payée ou annulée.');

        $data = $this->stripeService->createPaymentIntent($order);

        return response()->json($data);
    }

    /**
     * POST /api/orders/{order}/confirm-payment
     * Confirm payment after Stripe success.
     */
    public function confirmPayment(Request $request, \App\Models\Order $order)
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $request->validate(['payment_intent_id' => 'required|string']);

        $commissionRate = (int) config('marketplace.platform_commission', 10);
        $this->orderService->createPayments($order, $request->payment_intent_id, $commissionRate);

        return response()->json(['message' => 'Paiement confirmé.', 'order' => $order->fresh()]);
    }

    /**
     * GET /api/orders — buyer orders history
     */
    public function index(Request $request)
    {
        $orders = \App\Models\Order::with(['items.product'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }
}
