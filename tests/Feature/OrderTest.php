<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private function setupBuyerWithCart(int $qty = 2, int $stock = 10): array
    {
        $buyer      = User::factory()->buyer()->create();
        $sellerUser = User::factory()->seller()->create();
        $seller     = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
            'price'       => 10000,
            'stock'       => $stock,
        ]);
        Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => $qty]);

        return [$buyer, $product, $seller, $sellerUser];
    }

    public function test_buyer_can_place_order_from_cart(): void
    {
        Mail::fake();
        [$buyer] = $this->setupBuyerWithCart();

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/orders', ['delivery_address' => 'Rue 5, Tana'])
             ->assertStatus(201)
             ->assertJsonStructure(['order', 'message']);

        $this->assertDatabaseHas('orders', ['user_id' => $buyer->id]);
    }

    public function test_stock_is_decremented_after_order(): void
    {
        Mail::fake();
        [$buyer, $product] = $this->setupBuyerWithCart(qty: 3, stock: 10);

        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');

        $this->assertEquals(7, $product->fresh()->stock);
    }

    public function test_cart_is_cleared_after_order(): void
    {
        Mail::fake();
        [$buyer] = $this->setupBuyerWithCart();

        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');

        $this->assertEquals(0, Cart::where('user_id', $buyer->id)->count());
    }

    public function test_order_fails_when_insufficient_stock(): void
    {
        [$buyer] = $this->setupBuyerWithCart(qty: 5, stock: 2);

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/orders')
             ->assertStatus(422)
             ->assertJsonStructure(['errors']);
    }

    public function test_order_fails_with_empty_cart(): void
    {
        $buyer = User::factory()->buyer()->create();

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/orders')
             ->assertStatus(422);
    }

    public function test_confirmation_email_sent_on_order(): void
    {
        Mail::fake();
        [$buyer] = $this->setupBuyerWithCart();

        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');

        Mail::assertSent(\App\Mail\OrderConfirmationMail::class);
    }

    public function test_seller_can_update_order_status(): void
    {
        Mail::fake();
        [$buyer, $product, $seller, $sellerUser] = $this->setupBuyerWithCart();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');

        $order = Order::first();

        // Seller logs in via web session
        $this->actingAs($sellerUser)
             ->patch("/vendor/orders/{$order->id}/status/confirmed")
             ->assertRedirect();

        $this->assertEquals('confirmed', $order->fresh()->status);
    }

    public function test_buyer_can_view_their_orders(): void
    {
        Mail::fake();
        [$buyer] = $this->setupBuyerWithCart();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');

        $this->actingAs($buyer, 'sanctum')
             ->getJson('/api/orders')
             ->assertStatus(200)
             ->assertJsonStructure(['data'])
             ->assertJsonPath('total', 1);
    }

    public function test_buyer_cannot_update_order_status(): void
    {
        Mail::fake();
        [$buyer] = $this->setupBuyerWithCart();
        $this->actingAs($buyer, 'sanctum')->postJson('/api/orders');
        $order = Order::first();

        // Buyer should be blocked (role:seller,admin on web route)
        $this->actingAs($buyer)
             ->patch("/vendor/orders/{$order->id}/status/delivered")
             ->assertStatus(403);
    }
}
