<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_email_without_public_registration(): void
    {
        $this->get('/login')->assertOk()->assertPage('Auth/Login')->assertPageProp('auth.user', null);
        $this->get('/register')->assertNotFound();
        $this->post('/register', [])->assertNotFound();
    }

    public function test_user_can_login_with_normalized_email(): void
    {
        $user = User::factory()->create(['email' => 'team@example.com', 'role' => 'user']);
        $this->post('/login', ['email' => ' TEAM@EXAMPLE.COM ', 'password' => 'password'])->assertRedirect(route('reparations.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_name_is_no_longer_a_login_identifier(): void
    {
        $user = User::factory()->create();
        $this->post('/login', ['name' => $user->name, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_invalid_password_is_rejected_and_not_flashed(): void
    {
        $user = User::factory()->create();
        $this->from('/login')->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertRedirect('/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->assertGuest();
    }

    public function test_repeated_failures_are_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'wrong']);
        }
        $response = $this->post('/login', ['email' => 'blocked@example.com', 'password' => 'wrong']);
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Trop de tentatives', session('errors')->first('email'));
    }

    public function test_standard_user_cannot_manage_accounts(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $other = User::factory()->create();
        $this->actingAs($user);
        $this->get('/users')->assertForbidden();
        $this->get('/users/create')->assertForbidden();
        $this->post('/users', [])->assertForbidden();
        $this->put('/users/'.$other->id, [])->assertForbidden();
        $this->delete('/users/'.$other->id)->assertForbidden();
        $this->get('/admin')->assertForbidden();
    }

    public function test_admin_can_create_email_account_and_duplicates_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['name' => 'Technicien', 'email' => ' TECH@example.com ', 'password' => 'strong-password', 'password_confirmation' => 'strong-password', 'role' => 'user'];
        $this->post('/users', $data)->assertRedirect(route('users.edit', User::where('email', 'tech@example.com')->first()));
        $this->assertDatabaseHas('users', ['email' => 'tech@example.com', 'role' => 'user']);
        $data['name'] = 'Autre';
        $this->post('/users', $data)->assertSessionHasErrors('email');
    }

    public function test_last_admin_cannot_be_demoted_and_password_must_be_valid(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $this->put('/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])->assertSessionHasErrors('role');
        $this->put('/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'admin', 'password' => 'x'])->assertSessionHasErrors('password');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_logout_ends_session_and_guests_cannot_access_accounts(): void
    {
        $this->get('/users')->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
