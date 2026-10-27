<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_view_filter_and_edit_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['name' => 'Accueil', 'role' => 'user', 'is_active' => false]);
        $this->actingAs($admin)->get('/users')->assertOk()->assertPage('Users/Index')->assertPageProp('users.data', fn ($users) => collect($users)->contains('name', 'Accueil'));
        $this->get('/users?status=inactive&role=user&q=Accueil')->assertOk()->assertPageProp('users.total', 1)->assertPageProp('users.data.0.id', $user->id);
        $this->get('/users/create')->assertOk()->assertPage('Users/Form');
        $this->get('/users/'.$user->id.'/edit')->assertOk()->assertPageProp('user.name', 'Accueil');
    }

    public function test_confirmation_is_required_and_password_is_hashed(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['name' => 'Compte', 'email' => ' NEW@example.com ', 'role' => 'user', 'password' => 'temporary-password'];
        $this->post('/users', $data)->assertSessionHasErrors('password')->assertSessionMissing('_old_input.password');
        $data['password_confirmation'] = 'temporary-password';
        $this->post('/users', $data)->assertRedirect();
        $user = User::where('email', 'new@example.com')->sole();
        $this->assertTrue(Hash::check('temporary-password', $user->password));
        $this->assertTrue($user->is_active);
    }

    public function test_inactive_accounts_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_status_changes_revoke_sessions_and_reactivation_allows_login(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin)->patch('/users/'.$user->id.'/status', ['is_active' => 0])->assertRedirect('/users');
        $this->assertFalse($user->fresh()->is_active);
        $this->assertSame(1, $user->fresh()->session_version);
        $this->actingAs($user)->withSession(['account_version' => 0])->get('/clients')->assertRedirect('/login');
        $this->assertGuest();
        $this->actingAs($admin)->withSession(['account_version' => 0])->patch('/users/'.$user->id.'/status', ['is_active' => 1])->assertRedirect('/users');
        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('reparations.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->get('/clients')->assertOk();
    }

    public function test_password_change_revokes_old_sessions_and_preserves_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($admin)->put('/users/'.$user->id, ['name' => $user->name, 'email' => $user->email, 'role' => 'user', 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect('/users');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->actingAs($user)->withSession(['account_version' => 0])->get('/clients')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => $user->email, 'password' => 'new-password'])->assertRedirect();
        $this->get('/clients')->assertOk();
    }

    public function test_blank_password_is_preserved_and_self_disable_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $hash = $user->password;
        $this->actingAs($admin)->put('/users/'.$user->id, ['name' => 'Nom corrigé', 'email' => $user->email, 'role' => 'user', 'password' => ''])->assertRedirect('/users');
        $this->assertSame($hash, $user->fresh()->password);
        $this->assertSame(0, $user->fresh()->session_version);
        $this->patch('/users/'.$admin->id.'/status', ['is_active' => 0])->assertSessionHasErrors('account');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_disabled_admin_does_not_allow_last_active_admin_demotion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($admin)->put('/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_standard_user_cannot_change_account_status_and_deletion_preserves_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->patch('/users/'.$admin->id.'/status', ['is_active' => 0])->assertForbidden();
        $this->actingAs($admin)->delete('/users/'.$user->id)->assertRedirect('/users');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_active' => false]);
    }
}
