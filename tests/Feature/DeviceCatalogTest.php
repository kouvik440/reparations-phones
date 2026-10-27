<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesDeviceCatalog;
use Tests\TestCase;

class DeviceCatalogTest extends TestCase
{
    use CreatesDeviceCatalog, RefreshDatabase;

    private function depositData(array $selection): array
    {
        $client = Client::create(['prenom' => 'Client', 'nom' => 'Ticket', 'telephone' => '70000000', 'piece_identite' => 'PIECEPRIVEE']);

        return [...$selection, 'client_id' => $client->id, 'probleme' => 'Panne confidentielle', 'date_depot' => today()->toDateString()];
    }

    public function test_admin_can_create_search_rename_and_delete_unused_brands_and_models(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/catalogue/marques', ['name' => '  Samsung  '])->assertRedirect();
        $brand = DeviceBrand::sole();
        $this->assertSame('Samsung', $brand->name);
        $this->post('/catalogue/marques', ['name' => 'sAMsung'])->assertSessionHasErrors('normalized_name');
        $this->post('/catalogue/marques/'.$brand->id.'/modeles', ['name' => 'Galaxy A15'])->assertRedirect();
        $model = DeviceModel::sole();
        $this->post('/catalogue/marques/'.$brand->id.'/modeles', ['name' => 'galaxy a15'])->assertSessionHasErrors('normalized_name');
        $this->get('/catalogue?q=Sams&brand='.$brand->id)->assertOk()->assertPage('Catalog/Index')->assertPageProp('models.data.0.name', 'Galaxy A15');
        $this->get('/catalogue?q=Introuvable')->assertOk()->assertPageProp('brands.total', 0);
        $this->put('/catalogue/marques/'.$brand->id, ['name' => 'Samsung Mobile'])->assertRedirect();
        $this->put('/catalogue/marques/'.$brand->id.'/modeles/'.$model->id, ['name' => 'Galaxy A16'])->assertRedirect();
        $this->assertSame('Galaxy A16', $model->fresh()->name);
        $this->delete('/catalogue/marques/'.$brand->id)->assertSessionHasErrors('catalog');
        $this->delete('/catalogue/marques/'.$brand->id.'/modeles/'.$model->id)->assertRedirect();
        $this->delete('/catalogue/marques/'.$brand->id)->assertRedirect();
        $this->assertDatabaseCount('device_brands', 0);
        $this->assertDatabaseCount('device_models', 0);
    }

    public function test_new_deposit_requires_matching_catalog_ids_and_keeps_snapshot_names(): void
    {
        $selection = $this->deviceSelection();
        $other = $this->deviceSelection('Apple', 'iPhone 13');
        $data = $this->depositData($selection);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/reparations', [...$data, 'device_model_id' => $other['device_model_id']])->assertSessionHasErrors('device_model_id');
        $this->post('/reparations', [...$data, 'device_brand_id' => 9999])->assertSessionHasErrors('device_brand_id');
        $this->post('/reparations', [...$data, 'marque' => 'Fausse marque', 'modele' => 'Faux modèle'])->assertRedirect();
        $repair = Reparation::sole();
        $this->assertSame('Samsung', $repair->marque);
        $this->assertSame('A10', $repair->modele);
        $this->assertSame($selection['device_model_id'], $repair->device_model_id);
        $this->put('/catalogue/marques/'.$selection['device_brand_id'], ['name' => 'Nouveau nom'])->assertRedirect();
        $this->put('/catalogue/marques/'.$selection['device_brand_id'].'/modeles/'.$selection['device_model_id'], ['name' => 'Nouveau modèle'])->assertRedirect();
        $this->assertSame('Samsung', $repair->fresh()->marque);
        $this->assertSame('A10', $repair->fresh()->modele);
        $this->delete('/catalogue/marques/'.$selection['device_brand_id'].'/modeles/'.$selection['device_model_id'])->assertSessionHasErrors('catalog');
        $this->assertDatabaseCount('device_models', 2);
        $this->put('/catalogue/marques/'.$other['device_brand_id'].'/modeles/'.$selection['device_model_id'], ['name' => 'Mauvais rattachement'])->assertNotFound();
        $this->get('/reparations/create?client_id='.$data['client_id'])->assertOk()->assertPage('Repairs/Create')->assertPageProp('brands', fn ($brands) => collect($brands)->flatMap(fn ($brand) => $brand['device_models'])->contains('name', 'Nouveau modèle'));
    }

    public function test_catalog_management_permission_is_separate_from_deposit_and_login_has_a_landing_page(): void
    {
        $this->get('/catalogue')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/catalogue')->assertForbidden();
        $this->post('/catalogue/marques', ['name' => 'Interdit'])->assertForbidden();
        $role = Role::forceCreate(['name' => 'Catalogue', 'slug' => 'catalogue', 'permissions' => ['catalog.manage'], 'is_system' => false]);
        $manager = User::factory()->create(['role' => $role->slug]);
        $this->assertSame('catalog.index', $manager->fresh()->landingRoute());
        $this->actingAs($manager)->get('/catalogue')->assertOk();
        $this->get('/reparations/create')->assertForbidden();
    }

    public function test_migration_imports_existing_devices_without_rewriting_repair_history(): void
    {
        $migration = require database_path('migrations/2026_10_27_140000_create_device_catalog.php');
        $migration->down();
        $client = Client::create(['prenom' => 'Ancien', 'nom' => 'Client', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        $repair = Reparation::create(['client_id' => $client->id, 'marque' => ' Samsung ', 'modele' => 'Galaxy A15', 'probleme' => 'Écran', 'date_depot' => '2026-10-20', 'statut' => 'termine']);
        $code = $repair->code;
        $migration->up();
        $repair->refresh();
        $this->assertNotNull($repair->device_brand_id);
        $this->assertNotNull($repair->device_model_id);
        $this->assertSame(' Samsung ', $repair->marque);
        $this->assertSame('termine', $repair->statut);
        $this->assertSame($code, $repair->code);
        $this->assertDatabaseHas('device_brands', ['name' => 'Samsung']);
        $this->assertDatabaseCount('repair_status_changes', 0);
    }

    public function test_small_ticket_is_downloadable_pdf_and_tracking_has_a_return_link(): void
    {
        $this->actingAs(User::factory()->create());
        $this->post('/reparations', $this->depositData($this->deviceSelection()))->assertRedirect();
        $repair = Reparation::sole();
        $this->get('/reparations/'.$repair->id)->assertOk()->assertPage('Repairs/Show')->assertPageProp('reparation.id', $repair->id);
        $response = $this->get('/reparations/'.$repair->id.'/ticket')->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="ticket-'.$repair->code.'.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertGreaterThan(1000, strlen($response->getContent()));
        $this->post('/logout');
        $this->get('/reparations/'.$repair->id.'/ticket')->assertRedirect('/login');
        $role = Role::forceCreate(['name' => 'Sans dossiers', 'slug' => 'sans-dossiers', 'permissions' => [], 'is_system' => false]);
        $this->actingAs(User::factory()->create(['role' => $role->slug]))->get('/reparations/'.$repair->id.'/ticket')->assertForbidden();
        $this->post('/logout');
        $this->get('/suivi')->assertOk()->assertPage('Public/Tracking')->assertPageProp('searched', false);
        $this->post('/suivi', ['code' => $repair->code])->assertOk()->assertPage('Public/Tracking')->assertPageProp('searched', true);
    }
}
