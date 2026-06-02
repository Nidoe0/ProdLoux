<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
    }

    private function setupCart(int $qty = 2, int $stock = 10, float $price = 5000): array
    {
        $buyer      = User::factory()->buyer()->create();
        $sellerUser = User::factory()->seller()->create();
        $seller     = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
            'price'       => $price,
            'stock'       => $stock,
        ]);
        Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => $qty]);
        return [$buyer, $product, $seller];
    }

    public function test_creates_order_with_correct_total(): void
    {
        Mail::fake();
        [$buyer] = $this->setupCart(qty: 2, price: 5000);

        $order = $this->service->createFromCart($buyer->id);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals(10000, $order->total);
        $this->assertEquals(1, $order->items->count());
    }

    public function test_stock_is_decremented(): void
    {
        Mail::fake();
        [$buyer, $product] = $this->setupCart(qty: 3, stock: 10);

        $this->service->createFromCart($buyer->id);

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_cart_cleared_after_order(): void
    {
        Mail::fake();
        [$buyer] = $this->setupCart();

        $this->service->createFromCart($buyer->id);

        $this->assertEquals(0, Cart::where('user_id', $buyer->id)->count());
    }

    public function test_throws_validation_exception_on_insufficient_stock(): void
    {
        $this->expectException(ValidationException::class);
        [$buyer] = $this->setupCart(qty: 15, stock: 5);

        $this->service->createFromCart($buyer->id);
    }

    public function test_throws_when_cart_is_empty(): void
    {
        $this->expectException(ValidationException::class);
        $buyer = User::factory()->buyer()->create();

        $this->service->createFromCart($buyer->id);
    }

    public function test_stores_delivery_address_and_phone(): void
    {
        Mail::fake();
        [$buyer] = $this->setupCart();

        $order = $this->service->createFromCart($buyer->id, [
            'delivery_address' => 'Lot VF 12, Andohalo',
            'phone'            => '034 00 000 00',
        ]);

        $this->assertEquals('Lot VF 12, Andohalo', $order->delivery_address);
        $this->assertEquals('034 00 000 00', $order->phone);
    }

    public function test_create_payments_splits_correctly(): void
    {
        Mail::fake();
        [$buyer, $product, $seller] = $this->setupCart(qty: 2, price: 5000);
        $order = $this->service->createFromCart($buyer->id);

        $this->service->createPayments($order->load('items.product'), 'pi_test_abc', 10);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertEquals(10, $payment->commission_rate);
        $this->assertEquals(1000.00, $payment->commission_amount); // 10% of 10000
        $this->assertEquals(9000.00, $payment->seller_amount);     // 90% of 10000
        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_create_payments_with_different_commission_rate(): void
    {
        Mail::fake();
        [$buyer] = $this->setupCart(qty: 1, price: 10000);
        $order = $this->service->createFromCart($buyer->id);

        $this->service->createPayments($order->load('items.product'), 'pi_test_xyz', 15);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals(1500.00, $payment->commission_amount); // 15% of 10000
        $this->assertEquals(8500.00, $payment->seller_amount);
    }
}
