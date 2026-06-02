<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(): Seller
    {
        $user = User::factory()->seller()->create();
        return Seller::factory()->create(['user_id' => $user->id]);
    }

    private function createProduct(array $attrs = []): Product
    {
        $seller = $this->createSeller();
        $cat    = Category::factory()->create();
        return Product::factory()->create(array_merge([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
            'stock'       => 10,
        ], $attrs));
    }

    public function test_public_can_list_products_without_auth(): void
    {
        $this->createProduct();
        $this->getJson('/api/products')->assertStatus(200)->assertJsonStructure(['data', 'total']);
    }

    public function test_out_of_stock_products_excluded_from_list(): void
    {
        $this->createProduct(['stock' => 0]);

        $res = $this->getJson('/api/products');
        $this->assertEquals(0, $res->json('total'));
    }

    public function test_filter_by_category(): void
    {
        $seller = $this->createSeller();
        $cat1   = Category::factory()->create();
        $cat2   = Category::factory()->create();
        Product::factory()->create(['seller_id' => $seller->id, 'category_id' => $cat1->id, 'stock' => 5]);
        Product::factory()->create(['seller_id' => $seller->id, 'category_id' => $cat2->id, 'stock' => 5]);

        $res = $this->getJson('/api/products?category_id=' . $cat1->id);
        $this->assertEquals(1, $res->json('total'));
    }

    public function test_filter_by_gps_radius(): void
    {
        $seller = $this->createSeller();
        $cat    = Category::factory()->create();

        // Near Antananarivo
        Product::factory()->create([
            'seller_id' => $seller->id, 'category_id' => $cat->id,
            'latitude' => -18.91, 'longitude' => 47.53, 'stock' => 5,
        ]);
        // Far (Tamatave ~220 km)
        Product::factory()->create([
            'seller_id' => $seller->id, 'category_id' => $cat->id,
            'latitude' => -18.15, 'longitude' => 49.39, 'stock' => 5,
        ]);

        $res = $this->getJson('/api/products?latitude=-18.91&longitude=47.53&radius=5');
        $this->assertEquals(1, $res->json('total'));
    }

    public function test_product_show_returns_full_detail(): void
    {
        $product = $this->createProduct();
        $this->getJson('/api/products/' . $product->id)
             ->assertStatus(200)
             ->assertJsonFragment(['id' => $product->id]);
    }

    public function test_categories_list_is_public(): void
    {
        Category::factory()->count(3)->create();
        $this->getJson('/api/categories')
             ->assertStatus(200)
             ->assertJsonCount(3);
    }
}
