<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ── Admin ─────────────────────────────────────────────────────────────
        $admin = User::updateOrCreate(['email' => 'kotonirina45@gmail.com'], [
            'name'     => 'Nidot Admin',
            'password' => bcrypt('Koto2004@nidot'),
            'role'     => 'admin',
        ]);

        // ── Categories ────────────────────────────────────────────────────────
        $categoryNames = ['Fruits', 'Légumes', 'Artisanat', 'Épices', 'Boissons'];
        $categories    = [];
        foreach ($categoryNames as $name) {
            $categories[] = Category::firstOrCreate(['name' => $name]);
        }

        // ── 3 Buyers ─────────────────────────────────────────────────────────
        $buyers = [];
        for ($i = 1; $i <= 3; $i++) {
            $buyers[] = User::updateOrCreate(['email' => "acheteur$i@test.com"], [
                'name'     => "Acheteur $i",
                'password' => bcrypt('password'),
                'role'     => 'buyer',
            ]);
        }

        // ── 5 Sellers + 6 products each ───────────────────────────────────────
        $productData = [
            ['Vanille de Madagascar', 45000, 'Épices premium de la région SAVA'],
            ['Litchi frais', 8000, 'Litchis cueillis à maturité'],
            ['Raphia artisanat', 25000, 'Panier tressé à la main'],
            ['Poivre noir Sakalava', 15000, 'Poivre noir séché naturellement'],
            ['Rhum artisanal', 12000, 'Rhum local fait maison'],
            ['Tomate fraîche (1kg)', 3000, 'Tomates du jardin, sans pesticides'],
        ];

        $sellers = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::updateOrCreate(['email' => "vendeur$i@test.com"], [
                'name'     => "Vendeur $i",
                'password' => bcrypt('password'),
                'role'     => 'seller',
            ]);

            $seller = Seller::updateOrCreate(['user_id' => $user->id], [
                'shop_name'   => "Boutique Tsena $i",
                'description' => "Boutique numéro $i — produits locaux malgaches",
                'latitude'    => -18.9 + ($i * 0.02),
                'longitude'   => 47.5  + ($i * 0.02),
                'address'     => "Marché Analakely, Antananarivo — Stand $i",
            ]);

            $sellers[] = $seller;

            foreach ($productData as $idx => $pData) {
                $cat = $categories[$idx % count($categories)];
                Product::updateOrCreate(
                    ['seller_id' => $seller->id, 'name' => "$pData[0] — Boutique $i"],
                    [
                        'category_id' => $cat->id,
                        'description' => $pData[2],
                        'price'       => $pData[1] + rand(-1000, 2000),
                        'stock'       => $idx === 0 ? rand(1, 4) : rand(15, 80), // first product low stock
                        'latitude'    => -18.9 + ($i * 0.02) + (rand(-5, 5) / 1000),
                        'longitude'   => 47.5  + ($i * 0.02) + (rand(-5, 5) / 1000),
                    ]
                );
            }
        }

        // ── Sample orders ─────────────────────────────────────────────────────
        foreach ($buyers as $buyer) {
            for ($o = 0; $o < 3; $o++) {
                $seller  = $sellers[array_rand($sellers)];
                $prods   = Product::where('seller_id', $seller->id)->inRandomOrder()->take(rand(1, 3))->get();
                if ($prods->isEmpty()) continue;

                $items = $prods->map(fn($p) => ['product' => $p, 'qty' => rand(1, 3)]);
                $total = $items->sum(fn($i) => $i['product']->price * $i['qty']);

                $statuses = ['pending','confirmed','delivered','cancelled'];
                $status   = $statuses[array_rand($statuses)];

                $order = Order::create([
                    'user_id'          => $buyer->id,
                    'total'            => $total,
                    'status'           => $status,
                    'delivery_address' => 'Lot VF 12 Andohalo, Antananarivo 101',
                    'phone'            => '034' . rand(1000000, 9999999),
                    'confirmed_at'     => $status === 'confirmed' ? now() : null,
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product']->id,
                        'quantity'   => $item['qty'],
                        'price'      => $item['product']->price,
                    ]);
                }

                // Payment record for confirmed/delivered
                if (in_array($status, ['confirmed', 'delivered'])) {
                    $comm = round($total * 0.10, 2);
                    Payment::create([
                        'order_id'          => $order->id,
                        'seller_id'         => $seller->id,
                        'amount_total'      => $total,
                        'commission_amount' => $comm,
                        'seller_amount'     => $total - $comm,
                        'commission_rate'   => 10,
                        'status'            => 'transferred',
                    ]);
                }

                // Reviews on delivered orders
                if ($status === 'delivered') {
                    $reviewedProduct = $prods->first();
                    Review::firstOrCreate(
                        ['user_id' => $buyer->id, 'product_id' => $reviewedProduct->id, 'order_id' => $order->id],
                        [
                            'rating' => rand(3, 5),
                            'body'   => fake()->sentence(10),
                            'status' => 'approved',
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ Seeder terminé :');
        $this->command->info('   Admin    : kotonirina45@gmail.com / Koto2004@nidot');
        $this->command->info('   Vendeurs : vendeur1@test.com … vendeur5@test.com / password');
        $this->command->info('   Acheteurs: acheteur1@test.com … acheteur3@test.com / password');
        $this->command->info('   Produits : ' . Product::count() . ' | Commandes : ' . Order::count());
    }
}
