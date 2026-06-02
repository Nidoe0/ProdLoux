<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(int $stock = 20): array
    {
        $buyer      = User::factory()->buyer()->create();
        $sellerUser = User::factory()->seller()->create();
        $seller     = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
            'stock'       => $stock,
        ]);
        return [$buyer, $product];
    }

    public function test_buyer_can_add_product_to_cart(): void
    {
        [$buyer, $product] = $this->makeProduct();

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 2])
             ->assertStatus(201);

        $this->assertDatabaseHas('carts', ['user_id' => $buyer->id, 'product_id' => $product->id]);
    }

    public function test_cart_add_fails_when_stock_insufficient(): void
    {
        [$buyer, $product] = $this->makeProduct(stock: 1);

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/cart', ['product_id' => $product->id, 'quantity' => 10])
             ->assertStatus(422);
    }

    public function test_buyer_can_view_cart(): void
    {
        [$buyer, $product] = $this->makeProduct();
        Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer, 'sanctum')
             ->getJson('/api/cart')
             ->assertStatus(200)
             ->assertJsonStructure(['items', 'total']);
    }

    public function test_buyer_can_update_cart_quantity(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $cart = Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer, 'sanctum')
             ->putJson('/api/cart/' . $cart->id, ['quantity' => 5])
             ->assertStatus(200);

        $this->assertEquals(5, $cart->fresh()->quantity);
    }

    public function test_buyer_can_remove_cart_item(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $cart = Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer, 'sanctum')
             ->deleteJson('/api/cart/' . $cart->id)
             ->assertStatus(200);

        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
    }

    public function test_buyer_can_clear_entire_cart(): void
    {
        [$buyer, $product] = $this->makeProduct();
        Cart::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'quantity' => 2]);

        $this->actingAs($buyer, 'sanctum')
             ->deleteJson('/api/cart')
             ->assertStatus(200);

        $this->assertEquals(0, Cart::where('user_id', $buyer->id)->count());
    }

    public function test_seller_cannot_access_buyer_cart(): void
    {
        $sellerUser = User::factory()->seller()->create();
        Seller::factory()->create(['user_id' => $sellerUser->id]);

        $this->actingAs($sellerUser, 'sanctum')
             ->getJson('/api/cart')
             ->assertStatus(403);
    }

    public function test_buyer_cannot_modify_another_buyers_cart(): void
    {
        [$buyer1, $product] = $this->makeProduct();
        $buyer2 = User::factory()->buyer()->create();
        $cart   = Cart::create(['user_id' => $buyer1->id, 'product_id' => $product->id, 'quantity' => 1]);

        $this->actingAs($buyer2, 'sanctum')
             ->deleteJson('/api/cart/' . $cart->id)
             ->assertStatus(403);
    }
}
