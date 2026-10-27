<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesDeviceCatalog;
use Tests\TestCase;

class SpaTest extends TestCase
{
    use CreatesDeviceCatalog, RefreshDatabase;

    public function test_initial_response_has_one_react_shell_and_subsequent_visits_are_inertia_json(): void
    {
        $initial = $this->get('/')->assertOk()->assertPage('Public/Home');
        $initial->assertSee('id="app"', false)->assertDontSee('<h1', false);
        $version = $initial->inertiaPage()['version'];
        $this->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => $version, 'Accept' => 'text/html, application/xhtml+xml'])
            ->get('/suivi')->assertOk()->assertHeader('X-Inertia', 'true')->assertJsonPath('component', 'Public/Tracking')->assertJsonPath('props.searched', false)
            ->assertDontSee('<!DOCTYPE', false);
    }

    public function test_all_authenticated_screens_resolve_to_existing_react_components(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $client = Client::create(['prenom' => 'Client', 'nom' => 'SPA', 'telephone' => '70000000', 'piece_identite' => 'PIECE-PRIVEE']);
        $repair = Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A15', 'probleme' => 'Écran', 'date_depot' => today()->toDateString(), 'statut' => 'en_attente']);
        $role = Role::where('slug', 'user')->firstOrFail();
        $this->actingAs($admin);
        foreach ([
            '/dashboard' => 'Dashboard', '/clients' => 'Clients/Index', '/clients/create' => 'Clients/Create',
            '/clients/'.$client->id => 'Clients/Show', '/reparations' => 'Repairs/Index', '/reparations/find-client' => 'Repairs/FindClient',
            '/reparations/create?client_id='.$client->id => 'Repairs/Create', '/reparations/'.$repair->id => 'Repairs/Show',
            '/users' => 'Users/Index', '/users/create' => 'Users/Form', '/users/'.$admin->id.'/edit' => 'Users/Form',
            '/roles' => 'Roles/Index', '/roles/create' => 'Roles/Form', '/roles/'.$role->id.'/edit' => 'Roles/Form',
            '/catalogue' => 'Catalog/Index', '/acces-en-attente' => 'Auth/Pending',
        ] as $url => $component) {
            $this->get($url)->assertOk()->assertPage($component)->assertDontSee($admin->password, false);
        }
        $this->get('/reparations/'.$repair->id)->assertDontSee('PIECE-PRIVEE');
    }

    public function test_inertia_forms_receive_validation_errors_without_exposing_passwords(): void
    {
        $this->from('/login')->post('/login', ['email' => 'invalid', 'password' => 'private-secret'])->assertRedirect('/login')->assertSessionHasErrors('email')->assertSessionMissing('_old_input.password');
        $this->get('/login')->assertPage('Auth/Login')->assertPageProp('errors.email', fn ($error) => is_string($error) && $error !== '');
        $this->get('/login')->assertDontSee('private-secret');
    }

    public function test_public_tracking_props_are_an_explicit_allowlist(): void
    {
        $client = Client::create(['prenom' => 'Secret', 'nom' => 'Client', 'telephone' => '70000000', 'piece_identite' => 'PIECE-PRIVEE']);
        $repair = Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A15', 'probleme' => 'PANNE-PRIVEE', 'date_depot' => today()->toDateString(), 'statut' => 'en_attente']);
        $response = $this->post('/suivi', ['code' => $repair->code])->assertPage('Public/Tracking');
        $this->assertEqualsCanonicalizing(['code', 'marque', 'modele', 'date_depot', 'prix', 'statut'], array_keys($response->inertiaProps('reparation')));
        $response->assertDontSee('PIECE-PRIVEE')->assertDontSee('PANNE-PRIVEE')->assertDontSee('70000000');
    }

    public function test_access_failures_have_a_react_screen_and_still_enforce_http_status(): void
    {
        $this->get('/page-inexistante')->assertNotFound()->assertPage('Error')->assertPageProp('status', 404);
        $this->actingAs(User::factory()->create())->get('/users')->assertForbidden()->assertPage('Error')->assertPageProp('status', 403);
    }
}
