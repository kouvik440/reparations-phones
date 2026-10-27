<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientsTest extends TestCase
{
    use RefreshDatabase;

    private function client(array $data = []): Client
    {
        return Client::create(array_merge(['prenom' => 'Aicha', 'nom' => 'Ouedraogo', 'telephone' => '70000000', 'piece_identite' => 'TEST-IDENTITE'], $data));
    }

    public function test_guests_cannot_access_clients(): void
    {
        $this->get('/clients')->assertRedirect('/login');
        $this->post('/clients', [])->assertRedirect('/login');
    }

    public function test_directory_searches_full_name_and_formatted_phone(): void
    {
        $this->actingAs(User::factory()->create());
        $client = $this->client(['telephone' => '+226 70-00-00-00']);
        $other = $this->client(['prenom' => 'Moussa', 'telephone' => '71000000']);
        $this->get('/clients')->assertOk()->assertPage('Clients/Index')->assertPageProp('clients.total', 2);
        $this->get('/clients?q=Aicha+Ouedraogo')->assertOk()->assertPageProp('clients.total', 1)->assertPageProp('clients.data.0.id', $client->id);
        $this->get('/clients?q=70+00+00+00')->assertOk()->assertPageProp('clients.total', 1);
        $this->get('/clients?q=Introuvable')->assertOk()->assertPageProp('clients.total', 0);
    }

    public function test_filters_and_counts_reflect_repair_activity(): void
    {
        $this->actingAs(User::factory()->create());
        $client = $this->client();
        $this->client(['telephone' => '71000000']);
        Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Ecran', 'date_depot' => '2026-10-26', 'statut' => 'en_cours']);
        $this->get('/clients?filter=active')->assertOk()->assertPageProp('clients.total', 1)->assertPageProp('clients.data.0.active_count', 1);
        $this->get('/clients?filter=without')->assertOk()->assertPageProp('clients.total', 1)->assertPageProp('clients.data.0.id', fn ($id) => $id !== $client->id);
        $this->get('/clients/'.$client->id)->assertOk()->assertPage('Clients/Show')->assertPageProp('reparations.data.0.marque', 'Samsung');
    }

    public function test_creation_normalizes_phone_and_continues_to_deposit(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/clients/create')->assertOk()->assertPage('Clients/Create');
        $this->post('/clients', ['prenom' => ' Aicha ', 'nom' => ' Ouedraogo ', 'telephone' => '+226 70-00-00-00', 'piece_identite' => 'TEST', 'next' => 'repair', 'id' => 999])->assertRedirect(route('reparations.create', ['client_id' => Client::first()->id]));
        $client = Client::first();
        $this->assertSame('+22670000000', $client->telephone);
        $this->assertSame('Aicha', $client->prenom);
        $this->assertNotEquals(999, $client->id);
        $this->get(route('reparations.create', ['client_id' => $client->id]))->assertOk()->assertPageProp('client.prenom', 'Aicha');
    }

    public function test_legacy_phone_duplicates_and_invalid_data_are_rejected(): void
    {
        $this->actingAs(User::factory()->create());
        $this->client(['telephone' => '70 00-00-00']);
        $data = ['prenom' => 'Autre', 'nom' => 'Client', 'telephone' => '70000000', 'piece_identite' => 'TEST'];
        $this->post('/clients', $data)->assertSessionHasErrors('telephone');
        $data['telephone'] = 'invalid';
        $this->post('/clients', $data)->assertSessionHasErrors('telephone');
        $this->post('/clients', [])->assertSessionHasErrors(['prenom', 'nom', 'telephone', 'piece_identite']);
        $this->assertDatabaseCount('clients', 1);
    }

    public function test_profile_redirect_pagination_and_empty_directory(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/clients')->assertOk()->assertPageProp('clients.total', 0);
        $this->post('/clients', ['prenom' => 'Aicha', 'nom' => 'Ouedraogo', 'telephone' => '70000000', 'piece_identite' => 'TEST', 'next' => 'profile'])->assertRedirect(route('clients.show', Client::first()));
        for ($i = 1; $i <= 12; $i++) {
            $this->client(['telephone' => '7100'.str_pad((string) $i, 4, '0', STR_PAD_LEFT)]);
        }
        $this->get('/clients?q=Ouedraogo&filter=without')->assertOk()->assertPageProp('clients.total', 13)->assertPageProp('clients.data', fn ($items) => count($items) === 12)->assertPageProp('search', 'Ouedraogo');
        $this->get('/clients/9999')->assertNotFound();
    }
}
