<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReparationsTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\CreatesDeviceCatalog;

    public function test_ticket_price_and_public_tracking_do_not_expose_client_identity(): void
    {
        $user = User::factory()->create();
        $client = Client::create(['prenom' => 'SecretPrenom', 'nom' => 'SecretNom', 'telephone' => '70000000', 'piece_identite' => 'CONFIDENTIEL']);
        $this->actingAs($user)->post('/reparations', [...$this->deviceSelection(), 'client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Panne privée', 'date_depot' => now()->toDateString(), 'prix' => 15000])->assertRedirect();
        $repair = Reparation::firstOrFail();
        $this->get('/reparations/'.$repair->id)->assertOk()->assertPage('Repairs/Show')->assertPageProp('reparation.code', $repair->code)->assertPageProp('reparation.prix', 15000);
        $this->put('/reparations/'.$repair->id.'/prix', ['prix' => -1])->assertSessionHasErrors('prix');
        $this->assertEquals(15000, $repair->fresh()->prix);
        $this->put('/reparations/'.$repair->id.'/prix', ['prix' => 20000])->assertSessionHasNoErrors();
        $this->assertEquals(20000, $repair->fresh()->prix);
        $this->post('/logout');
        $this->get('/reparations/'.$repair->id)->assertRedirect('/login');
        $this->put('/reparations/'.$repair->id.'/prix', ['prix' => 1])->assertRedirect('/login');
        $this->get('/suivi')->assertOk()->assertPage('Public/Tracking');
        $this->post('/suivi', ['code' => strtolower($repair->code)])->assertOk()->assertPageProp('reparation.prix', 20000)->assertPageProp('reparation.statut', 'en_attente')->assertDontSee('SecretPrenom')->assertDontSee('70000000')->assertDontSee('CONFIDENTIEL')->assertDontSee('Panne privée');
        $repair->update(['statut' => 'termine']);
        $this->post('/suivi', ['code' => $repair->code])->assertOk()->assertPageProp('reparation.statut', 'termine');
        $this->post('/suivi', ['code' => 'ZZZZZZZZ'])->assertOk()->assertPageProp('reparation', null);
        $this->post('/suivi', ['code' => 'bad'])->assertSessionHasErrors('code');
        $repair->update(['prix' => null]);
        $this->post('/suivi', ['code' => $repair->code])->assertOk()->assertPageProp('reparation.prix', null);
    }

    public function test_find_client_normalizes_phone_and_offers_direct_deposit(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '+226 70-00-00-00', 'piece_identite' => 'TEST']);
        $this->get('/reparations/find-client')->assertOk()->assertPage('Repairs/FindClient');
        $this->post('/reparations/find-client', ['telephone' => '+226 (70) 00.00.00'])->assertOk()
            ->assertPageProp('clients.0.prenom', 'Aicha')
            ->assertPageProp('clients.0.id', $client->id);
        $this->post('/reparations/find-client', ['telephone' => '71000000'])->assertOk()->assertPageProp('clients', [])->assertPageProp('telephone', '71000000');
        $this->get('/clients/create?telephone=71000000')->assertOk()->assertPageProp('telephone', '71000000');
        $this->post('/reparations/find-client', ['telephone' => 'abc'])->assertSessionHasErrors('telephone');
        $this->post('/reparations/find-client', ['telephone' => '700'])->assertSessionHasErrors('telephone');
        $this->post('/reparations/find-client', ['telephone' => ['70000000']])->assertSessionHasErrors('telephone');
    }

    public function test_search_filter_and_pagination_keep_correct_counts(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        for ($i = 0; $i < 14; $i++) {
            Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Ecran cassé', 'date_depot' => '2026-01-01', 'statut' => $i === 0 ? 'termine' : 'en_cours']);
        }
        $this->get('/reparations?q=Aicha&statut=en_cours')->assertOk()
            ->assertPageProp('reparations.total', 13)->assertPageProp('reparations.data', fn ($items) => count($items) === 12)
            ->assertPageProp('stats.termine', 1)
            ->assertPageProp('search', 'Aicha')->assertPageProp('auth.permissions.repairs.change_status', true);
        $this->get('/reparations?q=Introuvable')->assertOk()->assertPageProp('reparations.total', 0);
        $this->get('/reparations?statut=invalide')->assertSessionHasErrors('statut');
    }

    public function test_deposit_ignores_injected_status_and_status_update_is_validated(): void
    {
        $this->actingAs(User::factory()->create());
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        $data = [...$this->deviceSelection('Apple', '13'), 'client_id' => $client->id, 'marque' => 'Apple', 'modele' => '13', 'probleme' => 'Ecran', 'date_depot' => now()->toDateString(), 'statut' => 'termine'];
        $this->post('/reparations', $data)->assertRedirect(route('reparations.show', Reparation::firstOrFail()));
        $repair = Reparation::firstOrFail();
        $this->assertSame('en_attente', $repair->statut);
        $this->put('/reparations/'.$repair->id.'/statut', ['statut' => 'invalide'])->assertSessionHasErrors('statut');
        $this->assertSame('en_attente', $repair->fresh()->statut);
        $this->put('/reparations/'.$repair->id.'/statut', ['statut' => 'en_cours', 'status_version' => 0])->assertSessionHasNoErrors();
        $this->assertSame('en_cours', $repair->fresh()->statut);
        $data['date_depot'] = now()->addDay()->toDateString();
        $this->post('/reparations', $data)->assertSessionHasErrors('date_depot');
    }

    public function test_guests_are_redirected_and_empty_list_is_helpful(): void
    {
        $this->get('/reparations')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/reparations')->assertOk()->assertPageProp('reparations.total', 0);
    }
}
