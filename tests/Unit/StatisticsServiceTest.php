<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Services\StatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatisticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatisticsService();
    }

    private function makeSeller(): Seller
    {
        $user = User::factory()->seller()->create();
        return Seller::factory()->create(['user_id' => $user->id]);
    }

    private function makePayment(Seller $seller, float $amount, string $status = 'transferred'): Payment
    {
        $buyer = User::factory()->buyer()->create();
        $order = Order::factory()->create(['user_id' => $buyer->id, 'total' => $amount]);
        return Payment::create([
            'order_id'          => $order->id,
            'seller_id'         => $seller->id,
            'amount_total'      => $amount,
            'commission_amount' => $amount * 0.10,
            'seller_amount'     => $amount * 0.90,
            'commission_rate'   => 10,
            'status'            => $status,
        ]);
    }

    public function test_seller_stats_has_required_keys(): void
    {
        $seller = $this->makeSeller();
        $data   = $this->service->forSeller($seller, 'month');

        foreach (['revenue', 'totalOrders', 'revenueByDay', 'topProducts', 'orderStatuses', 'lowStockProducts', 'returns'] as $key) {
            $this->assertArrayHasKey($key, $data, "Missing key: $key");
        }
    }

    public function test_revenue_sums_only_transferred_payments(): void
    {
        $seller = $this->makeSeller();
        $this->makePayment($seller, 50000, 'transferred');
        $this->makePayment($seller, 20000, 'pending'); // should NOT count

        $data = $this->service->forSeller($seller, 'month');

        $this->assertEquals(45000.0, $data['revenue']); // 90% of 50000
    }

    public function test_low_stock_products_detected_correctly(): void
    {
        $seller = $this->makeSeller();
        $cat    = Category::factory()->create();
        Product::factory()->create(['seller_id' => $seller->id, 'category_id' => $cat->id, 'stock' => 2]);  // low
        Product::factory()->create(['seller_id' => $seller->id, 'category_id' => $cat->id, 'stock' => 50]); // ok

        $data = $this->service->forSeller($seller, 'month');

        $this->assertEquals(1, $data['lowStockProducts']->count());
    }

    public function test_admin_stats_include_commission(): void
    {
        $seller = $this->makeSeller();
        $this->makePayment($seller, 100000, 'transferred');

        $data = $this->service->admin('month');

        $this->assertArrayHasKey('totalCommission', $data);
        $this->assertEquals(10000.0, $data['totalCommission']); // 10% of 100000
        $this->assertEquals(90000.0, $data['totalRevenue']);
    }

    public function test_admin_stats_counts_all_sellers_and_products(): void
    {
        $seller1 = $this->makeSeller();
        $seller2 = $this->makeSeller();
        $cat     = Category::factory()->create();
        Product::factory()->create(['seller_id' => $seller1->id, 'category_id' => $cat->id]);
        Product::factory()->create(['seller_id' => $seller2->id, 'category_id' => $cat->id]);

        $data = $this->service->admin('month');

        $this->assertEquals(2, $data['totalSellers']);
        $this->assertEquals(2, $data['totalProducts']);
    }

    public function test_stats_for_different_periods(): void
    {
        $seller = $this->makeSeller();

        foreach (['week', 'month', 'year', 'custom'] as $period) {
            $data = $this->service->forSeller($seller, $period);
            $this->assertArrayHasKey('revenue', $data);
        }
    }

    public function test_returns_count_cancelled_orders_only(): void
    {
        $seller     = $this->makeSeller();
        $buyer      = User::factory()->buyer()->create();
        $cat        = Category::factory()->create();
        $product    = Product::factory()->create(['seller_id' => $seller->id, 'category_id' => $cat->id]);

        $cancelledOrder  = Order::factory()->create(['user_id' => $buyer->id, 'status' => 'cancelled']);
        $deliveredOrder  = Order::factory()->create(['user_id' => $buyer->id, 'status' => 'delivered']);

        OrderItem::create(['order_id' => $cancelledOrder->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 1000]);
        OrderItem::create(['order_id' => $deliveredOrder->id, 'product_id' => $product->id, 'quantity' => 1, 'price' => 1000]);

        $data = $this->service->forSeller($seller, 'month');

        $this->assertEquals(1, $data['returns']);
    }
}
