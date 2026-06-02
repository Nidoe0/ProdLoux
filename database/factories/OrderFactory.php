<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'          => User::factory()->buyer(),
            'total'            => fake()->numberBetween(5000, 200000),
            'status'           => 'pending',
            'delivery_address' => fake()->streetAddress() . ', Antananarivo',
            'phone'            => '034' . fake()->numerify('#######'),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn () => ['status' => 'confirmed', 'confirmed_at' => now()]);
    }

    public function delivered(): static
    {
        return $this->state(fn () => ['status' => 'delivered']);
    }
}
