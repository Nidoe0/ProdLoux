<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): array
    {
        $buyer      = User::factory()->buyer()->create();
        $sellerUser = User::factory()->seller()->create();
        $seller     = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
        ]);
        return [$buyer, $product];
    }

    public function test_authenticated_user_can_submit_review(): void
    {
        [$buyer, $product] = $this->makeProduct();

        $this->actingAs($buyer, 'sanctum')
             ->postJson("/api/products/{$product->id}/reviews", [
                 'rating' => 4,
                 'body'   => 'Très bon produit !',
             ])
             ->assertStatus(201)
             ->assertJsonFragment(['message' => 'Avis soumis, en attente de modération.']);

        $this->assertDatabaseHas('reviews', [
            'user_id'    => $buyer->id,
            'product_id' => $product->id,
            'status'     => 'pending',
        ]);
    }

    public function test_review_rating_must_be_between_1_and_5(): void
    {
        [$buyer, $product] = $this->makeProduct();

        $this->actingAs($buyer, 'sanctum')
             ->postJson("/api/products/{$product->id}/reviews", ['rating' => 6])
             ->assertStatus(422);

        $this->actingAs($buyer, 'sanctum')
             ->postJson("/api/products/{$product->id}/reviews", ['rating' => 0])
             ->assertStatus(422);
    }

    public function test_cannot_submit_duplicate_review_same_product(): void
    {
        [$buyer, $product] = $this->makeProduct();
        Review::create([
            'user_id' => $buyer->id, 'product_id' => $product->id,
            'rating' => 3, 'status' => 'pending',
        ]);

        $this->actingAs($buyer, 'sanctum')
             ->postJson("/api/products/{$product->id}/reviews", ['rating' => 5])
             ->assertStatus(422);
    }

    public function test_user_can_flag_a_review(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $otherUser = User::factory()->buyer()->create();
        $review = Review::create([
            'user_id' => $otherUser->id, 'product_id' => $product->id,
            'rating' => 1, 'status' => 'approved',
        ]);

        $this->actingAs($buyer, 'sanctum')
             ->postJson("/api/reviews/{$review->id}/flag", ['reason' => 'Contenu offensant'])
             ->assertStatus(200);

        $this->assertTrue($review->fresh()->flagged);
        $this->assertEquals('Contenu offensant', $review->fresh()->flag_reason);
    }

    public function test_only_approved_reviews_in_public_list(): void
    {
        [$buyer, $product] = $this->makeProduct();
        Review::create(['user_id' => $buyer->id, 'product_id' => $product->id, 'rating' => 5, 'status' => 'approved']);
        $buyer2 = User::factory()->buyer()->create();
        Review::create(['user_id' => $buyer2->id, 'product_id' => $product->id, 'rating' => 2, 'status' => 'pending']);

        $res = $this->getJson("/api/products/{$product->id}/reviews");
        $res->assertStatus(200);
        $this->assertEquals(1, $res->json('count'));
    }

    public function test_admin_can_approve_review(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $admin  = User::factory()->admin()->create();
        $review = Review::create([
            'user_id' => $buyer->id, 'product_id' => $product->id,
            'rating' => 4, 'status' => 'pending',
        ]);

        $this->actingAs($admin)
             ->patch("/admin/reviews/{$review->id}/approve")
             ->assertRedirect();

        $this->assertEquals('approved', $review->fresh()->status);
    }

    public function test_admin_can_reject_review(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $admin  = User::factory()->admin()->create();
        $review = Review::create([
            'user_id' => $buyer->id, 'product_id' => $product->id,
            'rating' => 1, 'status' => 'pending',
        ]);

        $this->actingAs($admin)
             ->patch("/admin/reviews/{$review->id}/reject")
             ->assertRedirect();

        $this->assertEquals('rejected', $review->fresh()->status);
    }

    public function test_admin_can_delete_review(): void
    {
        [$buyer, $product] = $this->makeProduct();
        $admin  = User::factory()->admin()->create();
        $review = Review::create([
            'user_id' => $buyer->id, 'product_id' => $product->id,
            'rating' => 1, 'status' => 'flagged',
        ]);

        $this->actingAs($admin)
             ->delete("/admin/reviews/{$review->id}")
             ->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_unauthenticated_cannot_submit_review(): void
    {
        [, $product] = $this->makeProduct();

        $this->postJson("/api/products/{$product->id}/reviews", ['rating' => 4])
             ->assertStatus(401);
    }
}
