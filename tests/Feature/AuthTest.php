<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_register(): void
    {
        $res = $this->postJson('/api/register', [
            'name'                  => 'Test Buyer',
            'email'                 => 'buyer@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'buyer',
        ]);

        $res->assertStatus(201)->assertJsonStructure(['token', 'user']);
        $this->assertDatabaseHas('users', ['email' => 'buyer@test.com', 'role' => 'buyer']);
    }

    public function test_seller_can_register_with_shop(): void
    {
        $res = $this->postJson('/api/register', [
            'name'                  => 'Test Seller',
            'email'                 => 'seller@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'seller',
            'shop_name'             => 'Ma Boutique Test',
        ]);

        $res->assertStatus(201);
        $this->assertDatabaseHas('sellers', ['shop_name' => 'Ma Boutique Test']);
    }

    public function test_seller_register_without_shop_name_fails(): void
    {
        $this->postJson('/api/register', [
            'name'                  => 'Seller No Shop',
            'email'                 => 'noshop@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'seller',
            // shop_name missing
        ])->assertStatus(422);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->buyer()->create(['password' => bcrypt('secret123')]);

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'secret123',
        ])->assertStatus(200)->assertJsonStructure(['token', 'user']);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create(['password' => bcrypt('correct')]);

        $this->postJson('/api/login', [
            'email'    => $user->email,
            'password' => 'wrong_password',
        ])->assertStatus(401);
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
             ->postJson('/api/logout')
             ->assertStatus(200);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
             ->getJson('/api/me')
             ->assertStatus(200)
             ->assertJsonFragment(['email' => $user->email]);
    }

    public function test_register_requires_unique_email(): void
    {
        User::factory()->create(['email' => 'taken@test.com']);

        $this->postJson('/api/register', [
            'name'                  => 'Another',
            'email'                 => 'taken@test.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'role'                  => 'buyer',
        ])->assertStatus(422);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }
}
