<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_home_page()
    {
        $response = $this->get(route('home'));
        $response->assertStatus(200);
    }

    public function test_guest_can_see_sign_in_page()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200)
            ->assertViewIs('auth.login');
    }

    public function test_guest_can_see_sign_up_page()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200)
            ->assertViewIs('auth.register');
    }

    public function test_guest_can_register()
    {
        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'invalid@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_dashboard()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_see_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }
}
