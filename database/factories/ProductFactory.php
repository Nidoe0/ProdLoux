<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'seller_id'   => Seller::factory(),
            'category_id' => Category::factory(),
            'name'        => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price'       => fake()->numberBetween(1000, 100000),
            'stock'       => fake()->numberBetween(5, 50),
            'latitude'    => fake()->randomFloat(7, -20.5, -11.9),
            'longitude'   => fake()->randomFloat(7, 43.2, 50.5),
        ];
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => fake()->numberBetween(1, 3)]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }
}
