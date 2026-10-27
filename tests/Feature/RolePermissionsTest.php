<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\CreatesDeviceCatalog;

    private function role(array $permissions): Role
    {
        $role = new Role;
        $role->slug = 'test-'.fake()->uuid();
        $role->fill(['name' => 'Rôle '.fake()->uuid(), 'permissions' => $permissions]);
        $role->save();

        return $role;
    }

    private function repair(): Reparation
    {
        $client = Client::create(['prenom' => 'ClientPrive', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);

        return Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Panne privée', 'date_depot' => today()->toDateString(), 'statut' => 'en_attente']);
    }

    public function test_admin_can_create_role_from_catalog_and_assign_it_to_an_account(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/roles')->assertOk()->assertPage('Roles/Index');
        $this->get('/roles/create')->assertOk()->assertPage('Roles/Form')->assertPageProp('groups', fn ($groups) => $groups['Clients']['clients.view']['label'] === 'Consulter les clients');
        $this->post('/roles', ['name' => ' Consultation ', 'description' => 'Lecture seule', 'permissions' => ['clients.view', 'repairs.view'], 'slug' => 'admin', 'is_system' => true])->assertRedirect('/roles');
        $role = Role::where('name', 'Consultation')->sole();
        $this->assertNotSame('admin', $role->slug);
        $this->assertFalse($role->is_system);
        $this->get('/roles/'.$role->id.'/edit')->assertOk()->assertPageProp('role.description', 'Lecture seule');
        $this->post('/users', ['name' => 'Lecteur', 'email' => 'lecteur@example.com', 'role' => $role->slug, 'password' => 'strong-password', 'password_confirmation' => 'strong-password'])->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'lecteur@example.com', 'role' => $role->slug]);
        $this->get('/users?role='.$role->slug)->assertOk()->assertPageProp('users.total', 1)->assertPageProp('users.data.0.role_name', 'Consultation');
    }

    public function test_standard_user_and_guest_cannot_manage_roles(): void
    {
        $role = $this->role(['clients.view']);
        $this->get('/roles')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'user']));
        $this->get('/roles')->assertForbidden();
        $this->get('/roles/create')->assertForbidden();
        $this->post('/roles', ['name' => 'Interdit', 'permissions' => ['roles.manage']])->assertForbidden();
        $this->get('/roles/'.$role->id.'/edit')->assertForbidden();
        $this->put('/roles/'.$role->id, ['name' => 'Interdit', 'permissions' => []])->assertForbidden();
    }

    public function test_unknown_permissions_duplicates_and_missing_dependencies_are_rejected(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/roles', ['name' => 'Invalide', 'permissions' => ['server.delete']])->assertSessionHasErrors('permissions.0');
        $this->post('/roles', ['name' => 'Invalide', 'permissions' => 'all'])->assertSessionHasErrors('permissions');
        $this->post('/roles', ['name' => 'Invalide', 'permissions' => ['repairs.create']])->assertSessionHasErrors('permissions');
        $this->post('/roles', ['name' => 'Administrateur', 'permissions' => []])->assertSessionHasErrors('name');
        $this->post('/roles', ['name' => 'Invalide', 'permissions' => ['clients.view', 'clients.view']])->assertSessionHasErrors();
        $this->assertDatabaseCount('roles', 2);
    }

    public function test_protected_admin_role_cannot_be_edited_even_by_admin(): void
    {
        $adminRole = Role::where('slug', 'admin')->sole();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/roles/'.$adminRole->id.'/edit')->assertForbidden();
        $this->put('/roles/'.$adminRole->id, ['name' => 'Autre', 'permissions' => []])->assertForbidden();
        $this->assertSame('Administrateur', $adminRole->fresh()->name);
    }

    public function test_read_only_permissions_deny_writes_and_hide_buttons(): void
    {
        $repair = $this->repair();
        $role = $this->role(['clients.view', 'repairs.view']);
        $user = User::factory()->create(['role' => $role->slug]);
        $this->actingAs($user)->get('/clients')->assertOk()->assertPageProp('auth.permissions.clients.create', false)->assertPageProp('auth.permissions.repairs.create', false);
        $this->get('/reparations')->assertOk()->assertPageProp('auth.permissions.repairs.change_status', false)->assertPageProp('auth.permissions.repairs.create', false);
        $this->get('/reparations/'.$repair->id)->assertOk()->assertPageProp('auth.permissions.repairs.set_price', false);
        $this->get('/dashboard')->assertForbidden();
        $this->get('/clients/create')->assertForbidden();
        $this->post('/clients', [])->assertForbidden();
        $this->get('/reparations/create')->assertForbidden();
        $this->get('/reparations/find-client')->assertForbidden();
        $this->post('/reparations/find-client', ['telephone' => '70000000'])->assertForbidden();
        $this->post('/reparations', [])->assertForbidden();
        $this->put('/reparations/'.$repair->id.'/statut', ['statut' => 'termine'])->assertForbidden();
        $this->put('/reparations/'.$repair->id.'/prix', ['prix' => 100])->assertForbidden();
        $this->assertSame('en_attente', $repair->fresh()->statut);
        $this->assertNull($repair->fresh()->prix);
    }

    public function test_role_permission_change_revokes_sessions_and_new_login_uses_new_permissions(): void
    {
        $role = $this->role(['clients.view', 'repairs.view']);
        $user = User::factory()->create(['role' => $role->slug]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->put('/roles/'.$role->id, ['name' => $role->name, 'permissions' => ['clients.view']])->assertRedirect('/roles');
        $this->assertSame(1, $user->fresh()->session_version);
        $this->actingAs($user)->withSession(['account_version' => 0])->get('/clients')->assertRedirect('/login');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/clients');
        $this->get('/clients')->assertOk();
        $this->get('/reparations')->assertForbidden();
    }

    public function test_role_metadata_change_preserves_sessions_and_slug(): void
    {
        $role = $this->role(['clients.view']);
        $user = User::factory()->create(['role' => $role->slug]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->put('/roles/'.$role->id, ['name' => 'Nom changé', 'permissions' => ['clients.view'], 'slug' => 'admin'])->assertRedirect('/roles');
        $this->assertSame(0, $user->fresh()->session_version);
        $this->assertSame($role->slug, $role->fresh()->slug);
    }

    public function test_no_permission_role_has_safe_login_landing(): void
    {
        $role = $this->role([]);
        $user = User::factory()->create(['role' => $role->slug]);
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect(route('access.pending'));
        $this->get('/acces-en-attente')->assertOk()->assertPage('Auth/Pending');
        $this->get('/dashboard')->assertForbidden();
        $this->get('/clients')->assertForbidden();
    }

    public function test_account_manager_cannot_promote_itself_or_modify_admin_accounts(): void
    {
        $role = $this->role(['users.manage']);
        $manager = User::factory()->create(['role' => $role->slug]);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($manager)->get('/users')->assertOk();
        $this->get('/users/'.$admin->id.'/edit')->assertForbidden();
        $this->put('/users/'.$admin->id, ['name' => $admin->name, 'email' => $admin->email, 'role' => 'user'])->assertForbidden();
        $this->patch('/users/'.$admin->id.'/status', ['is_active' => 0])->assertForbidden();
        $this->put('/users/'.$manager->id, ['name' => $manager->name, 'email' => $manager->email, 'role' => 'admin'])->assertSessionHasErrors('role');
        $this->get('/roles')->assertForbidden();
        $privileged = $this->role(['roles.manage']);
        $this->put('/users/'.$manager->id, ['name' => $manager->name, 'email' => $manager->email, 'role' => $privileged->slug])->assertSessionHasErrors('role');
        $privilegedUser = User::factory()->create(['role' => $privileged->slug]);
        $this->get('/users/'.$privilegedUser->id.'/edit')->assertForbidden();
        $this->patch('/users/'.$privilegedUser->id.'/status', ['is_active' => 0])->assertForbidden();
    }

    public function test_deposit_permission_does_not_allow_price_injection(): void
    {
        $repair = $this->repair();
        $role = $this->role(['clients.view', 'repairs.view', 'repairs.create']);
        $this->actingAs(User::factory()->create(['role' => $role->slug]));
        $this->get('/reparations/create?client_id='.$repair->client_id)->assertOk()->assertPageProp('auth.permissions.repairs.set_price', false);
        $this->post('/reparations', [...$this->deviceSelection('Apple', 'iPhone'), 'client_id' => $repair->client_id, 'marque' => 'Apple', 'modele' => 'iPhone', 'probleme' => 'Ecran', 'date_depot' => today()->toDateString(), 'prix' => 12345])->assertRedirect();
        $this->assertNull(Reparation::latest('id')->first()->prix);
    }

    public function test_dashboard_permission_does_not_expose_repair_or_client_details(): void
    {
        $this->repair();
        $role = $this->role(['dashboard.view']);
        $this->actingAs(User::factory()->create(['role' => $role->slug]))->get('/dashboard')->assertOk()->assertDontSee('ClientPrive')->assertPageProp('auth.permissions.repairs.view', false)->assertPageProp('recentRepairs', []);
    }

    public function test_role_manager_can_manage_roles_without_account_management(): void
    {
        $role = $this->role(['roles.manage']);
        $this->actingAs(User::factory()->create(['role' => $role->slug]));
        $this->get('/roles')->assertOk();
        $this->post('/roles', ['name' => 'Accueil', 'permissions' => ['clients.view', 'clients.create']])->assertRedirect('/roles');
        $this->get('/users')->assertForbidden();
    }
}
