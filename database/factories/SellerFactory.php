<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SellerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory()->seller(),
            'shop_name'   => fake()->company(),
            'description' => fake()->sentence(),
            'latitude'    => fake()->randomFloat(7, -20.5, -11.9),
            'longitude'   => fake()->randomFloat(7, 43.2, 50.5),
            'address'     => fake()->streetAddress() . ', Antananarivo',
        ];
    }
}
