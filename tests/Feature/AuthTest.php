<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $extra = []): array
    {
        return array_merge([
            'name' => 'Иван Петров',
            'email' => 'ivan@example.com',
            'phone' => '+79991234567',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ], $extra);
    }

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', $this->data());

        $response->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'ivan@example.com', 'role' => 'user']);
    }

    public function test_password_is_stored_as_hash(): void
    {
        $this->post('/register', $this->data());

        $user = User::where('email', 'ivan@example.com')->first();
        $this->assertNotEquals('Secret123', $user->password);
        $this->assertTrue(password_verify('Secret123', $user->password));
    }

    public function test_register_rejects_weak_password(): void
    {
        $this->post('/register', $this->data(['password' => '12345678', 'password_confirmation' => '12345678']))
            ->assertSessionHasErrors('password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'ivan@example.com']);

        $this->post('/register', $this->data())->assertSessionHasErrors('email');
    }

    public function test_register_rejects_bad_phone(): void
    {
        $this->post('/register', $this->data(['phone' => 'abc123']))->assertSessionHasErrors('phone');
    }

    public function test_user_cannot_make_himself_admin(): void
    {
        $this->post('/register', $this->data(['role' => 'admin']));

        $this->assertDatabaseHas('users', ['email' => 'ivan@example.com', 'role' => 'user']);
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'password1'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_login_with_wrong_password_fails(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_limited_after_five_attempts(): void
    {
        User::factory()->create(['email' => 'a@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'a@example.com', 'password' => 'wrong']);
        }

        $this->post('/login', ['email' => 'a@example.com', 'password' => 'password1'])->assertStatus(429);
    }

    public function test_post_without_csrf_token_is_rejected(): void
    {
        // в тестах проверка CSRF по умолчанию выключена, включаем её обратно
        $this->app['env'] = 'production';
        $this->post('/login', ['email' => 'a@example.com', 'password' => 'x'])->assertStatus(419);
    }
}
