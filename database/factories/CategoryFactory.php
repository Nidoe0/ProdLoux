<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        // Use unique random word to avoid exhaustion issues
        return [
            'name' => ucfirst(fake()->unique()->word()),
        ];
    }
}
