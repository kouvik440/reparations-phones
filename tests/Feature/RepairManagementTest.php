<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepairManagementTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\CreatesDeviceCatalog;

    public function test_standard_role_has_management_permissions_and_custom_roles_remain_explicit(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/reparations')->assertOk()
            ->assertPageProp('auth.permissions.repairs.edit', true)
            ->assertPageProp('auth.permissions.repairs.delete', true);
        $role = Role::forceCreate(['name' => 'Modification', 'slug' => 'edit-only', 'permissions' => ['repairs.view', 'repairs.edit'], 'is_system' => false]);
        $this->actingAs(User::factory()->create(['role' => $role->slug]));
        $this->get('/reparations')->assertOk()
            ->assertPageProp('auth.permissions.repairs.edit', true)
            ->assertPageProp('auth.permissions.repairs.delete', false);
    }

    public function test_edit_preserves_code_status_and_checks_version_and_input(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        $data = [...$this->deviceSelection(), 'client_id' => $client->id, 'probleme' => 'Ecran', 'date_depot' => today()->toDateString(), 'prix' => 5000];
        $this->post('/reparations', $data)->assertRedirect();
        $repair = Reparation::sole();
        $code = $repair->code;
        $this->get('/reparations/'.$repair->id.'/edit')->assertOk()->assertPage('Repairs/Create')->assertPageProp('repair.code', $code);
        $data = [...$data, 'probleme' => 'Batterie', 'prix' => 7000, 'status_version' => 0, 'code' => 'INJECTED', 'statut' => 'restitue'];
        $this->put('/reparations/'.$repair->id, $data)->assertRedirect('/reparations/'.$repair->id);
        $this->assertSame('Batterie', $repair->refresh()->probleme);
        $this->assertSame($code, $repair->code);
        $this->assertSame('en_attente', $repair->statut);
        $this->assertEquals(7000, $repair->prix);
        $this->putJson('/reparations/'.$repair->id, $data)->assertConflict();
        $this->putJson('/reparations/'.$repair->id, [...$data, 'status_version' => 1, 'device_model_id' => 99999])->assertUnprocessable();
    }

    public function test_deletion_requires_confirmation_and_removes_tracking_but_keeps_client_and_audit(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        $this->post('/reparations', [...$this->deviceSelection(), 'client_id' => $client->id, 'probleme' => 'Ecran', 'date_depot' => today()->toDateString()]);
        $repair = Reparation::sole();
        $this->delete('/reparations/'.$repair->id, ['confirmation' => 'wrong'])->assertSessionHasErrors('confirmation');
        $this->assertNotNull(Reparation::find($repair->id));
        $this->delete('/reparations/'.$repair->id, ['confirmation' => $repair->code])->assertRedirect('/reparations');
        $this->assertSoftDeleted('reparations', ['id' => $repair->id]);
        $this->assertDatabaseHas('clients', ['id' => $client->id]);
        $this->assertDatabaseHas('repair_status_changes', ['reparation_id' => $repair->id]);
        $this->get('/reparations/'.$repair->id)->assertNotFound();
        $this->post('/suivi', ['code' => $repair->code])->assertPageProp('reparation', null);
    }

    public function test_read_only_cannot_edit_or_delete(): void
    {
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        $repair = Reparation::create(['client_id' => $client->id, 'marque' => 'Apple', 'modele' => '13', 'probleme' => 'Ecran', 'date_depot' => today()->toDateString()]);
        $role = Role::forceCreate(['name' => 'Lecture', 'slug' => 'lecture', 'permissions' => ['repairs.view'], 'is_system' => false]);
        $this->actingAs(User::factory()->create(['role' => $role->slug]));
        $this->get('/reparations/'.$repair->id.'/edit')->assertForbidden();
        $this->put('/reparations/'.$repair->id, [])->assertForbidden();
        $this->delete('/reparations/'.$repair->id, ['confirmation' => $repair->code])->assertForbidden();
    }
}
