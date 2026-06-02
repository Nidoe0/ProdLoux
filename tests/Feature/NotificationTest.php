<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeSellerWithProduct(int $stock = 2): array
    {
        $sellerUser = User::factory()->seller()->create();
        $seller     = Seller::factory()->create(['user_id' => $sellerUser->id]);
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create([
            'seller_id'   => $seller->id,
            'category_id' => $cat->id,
            'stock'       => $stock,
            'name'        => 'Produit Test',
        ]);
        return [$sellerUser, $seller, $product];
    }

    public function test_seller_receives_low_stock_notification(): void
    {
        Notification::fake();
        [$sellerUser, , $product] = $this->makeSellerWithProduct();

        $sellerUser->notify(new LowStockNotification($product));

        Notification::assertSentTo($sellerUser, LowStockNotification::class);
    }

    public function test_low_stock_notification_database_data_correct(): void
    {
        [$sellerUser, , $product] = $this->makeSellerWithProduct(stock: 3);

        $sellerUser->notify(new LowStockNotification($product));

        $notif = $sellerUser->notifications()->first();
        $this->assertEquals('low_stock', $notif->data['type']);
        $this->assertEquals('Produit Test', $notif->data['product_name']);
        $this->assertEquals(3, $notif->data['stock']);
    }

    public function test_unread_count_endpoint_returns_correct_count(): void
    {
        [$sellerUser, , $product] = $this->makeSellerWithProduct();
        $sellerUser->notify(new LowStockNotification($product));
        $sellerUser->notify(new LowStockNotification($product));

        $res = $this->actingAs($sellerUser, 'sanctum')
                    ->getJson('/api/notifications/unread-count');

        $res->assertStatus(200)->assertJsonPath('count', 2);
    }

    public function test_mark_notification_as_read(): void
    {
        [$sellerUser, , $product] = $this->makeSellerWithProduct();
        $sellerUser->notify(new LowStockNotification($product));
        $notifId = $sellerUser->notifications()->first()->id;

        $this->actingAs($sellerUser, 'sanctum')
             ->postJson("/api/notifications/{$notifId}/read")
             ->assertStatus(200);

        $this->assertEquals(0, $sellerUser->unreadNotifications()->count());
    }

    public function test_mark_all_notifications_as_read(): void
    {
        [$sellerUser, , $product] = $this->makeSellerWithProduct();
        $sellerUser->notify(new LowStockNotification($product));
        $sellerUser->notify(new LowStockNotification($product));
        $sellerUser->notify(new LowStockNotification($product));

        $this->actingAs($sellerUser, 'sanctum')
             ->postJson('/api/notifications/read-all')
             ->assertStatus(200);

        $this->assertEquals(0, $sellerUser->unreadNotifications()->count());
    }

    public function test_notifications_list_is_paginated(): void
    {
        [$sellerUser, , $product] = $this->makeSellerWithProduct();
        for ($i = 0; $i < 5; $i++) {
            $sellerUser->notify(new LowStockNotification($product));
        }

        $res = $this->actingAs($sellerUser, 'sanctum')
                    ->getJson('/api/notifications');

        $res->assertStatus(200)->assertJsonStructure(['data', 'total']);
    }
}
