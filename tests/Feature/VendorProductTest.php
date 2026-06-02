<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorProductTest extends TestCase
{
    use RefreshDatabase;

    private function makeSeller(): array
    {
        $user   = User::factory()->seller()->create();
        $seller = Seller::factory()->create(['user_id' => $user->id]);
        return [$user, $seller];
    }

    private function makeProduct(Seller $seller): Product
    {
        return Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => Category::factory()->create()->id,
        ]);
    }

    public function test_seller_can_list_own_products(): void
    {
        [$user, $seller] = $this->makeSeller();
        $this->makeProduct($seller);
        $this->makeProduct($seller);

        $this->actingAs($user, 'sanctum')
             ->getJson('/api/vendor/products')
             ->assertStatus(200)
             ->assertJsonCount(2);
    }

    public function test_seller_can_create_product(): void
    {
        Storage::fake('public');
        [$user, $seller] = $this->makeSeller();
        $cat = Category::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->postJson('/api/vendor/products', [
                 'name'        => 'Vanille Premium',
                 'category_id' => $cat->id,
                 'price'       => 45000,
                 'stock'       => 20,
                 'description' => 'Vanille de Madagascar',
             ])
             ->assertStatus(201)
             ->assertJsonFragment(['message' => 'Produit créé.']);

        $this->assertDatabaseHas('products', ['name' => 'Vanille Premium', 'seller_id' => $seller->id]);
    }

    public function test_seller_can_update_own_product(): void
    {
        [$user, $seller] = $this->makeSeller();
        $product = $this->makeProduct($seller);
        $cat     = Category::factory()->create();

        $this->actingAs($user, 'sanctum')
             ->putJson('/api/vendor/products/' . $product->id, [
                 'name'        => 'Produit Modifié',
                 'category_id' => $cat->id,
                 'price'       => 9999,
                 'stock'       => 5,
             ])
             ->assertStatus(200);

        $this->assertEquals('Produit Modifié', $product->fresh()->name);
    }

    public function test_seller_cannot_update_another_sellers_product(): void
    {
        [$user1, $seller1] = $this->makeSeller();
        [$user2, $seller2] = $this->makeSeller();
        $product  = $this->makeProduct($seller2);
        $cat      = Category::factory()->create();

        $this->actingAs($user1, 'sanctum')
             ->putJson('/api/vendor/products/' . $product->id, [
                 'name' => 'Hacked', 'category_id' => $cat->id, 'price' => 1, 'stock' => 1,
             ])
             ->assertStatus(403);
    }

    public function test_seller_can_delete_own_product(): void
    {
        [$user, $seller] = $this->makeSeller();
        $product = $this->makeProduct($seller);

        $this->actingAs($user, 'sanctum')
             ->deleteJson('/api/vendor/products/' . $product->id)
             ->assertStatus(200);

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_seller_cannot_delete_another_sellers_product(): void
    {
        [$user1]        = $this->makeSeller();
        [$user2, $s2]   = $this->makeSeller();
        $product        = $this->makeProduct($s2);

        $this->actingAs($user1, 'sanctum')
             ->deleteJson('/api/vendor/products/' . $product->id)
             ->assertStatus(403);
    }

    public function test_buyer_cannot_create_product(): void
    {
        $buyer = User::factory()->buyer()->create();
        $cat   = Category::factory()->create();

        $this->actingAs($buyer, 'sanctum')
             ->postJson('/api/vendor/products', [
                 'name' => 'Test', 'category_id' => $cat->id, 'price' => 100, 'stock' => 1,
             ])
             ->assertStatus(403);
    }

    public function test_admin_can_create_product_for_any_seller(): void
    {
        Storage::fake('public');
        $admin  = User::factory()->admin()->create();
        [, $seller] = $this->makeSeller();
        $cat    = Category::factory()->create();

        $this->actingAs($admin, 'sanctum')
             ->postJson('/api/vendor/products', [
                 'name'        => 'Produit Admin',
                 'seller_id'   => $seller->id,
                 'category_id' => $cat->id,
                 'price'       => 5000,
                 'stock'       => 10,
             ])
             ->assertStatus(201);
    }
}
