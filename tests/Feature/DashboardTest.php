<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Reparation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(Carbon::parse('2026-10-26 14:00:00'));
    }

    public function test_guests_are_redirected_and_empty_dashboard_is_usable(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/dashboard')->assertOk()
            ->assertPage('Dashboard')
            ->assertPageProp('auth.permissions.users.manage', false)->assertPageProp('totalRepairs', 0)->assertPageProp('weekTotal', 0);
    }

    public function test_counts_and_chart_use_deposit_dates_and_real_statuses(): void
    {
        $client = Client::create(['prenom' => 'Aicha', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        foreach ([['2026-10-26', 'en_attente'], ['2026-10-26', 'en_cours'], ['2026-10-20', 'termine'], ['2026-10-19', 'en_attente'], ['2026-10-13', 'termine'], ['2026-10-12', 'termine']] as [$date, $status]) {
            Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Ecran', 'date_depot' => $date, 'statut' => $status]);
        }
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/dashboard');
        $this->get('/reparations?date=2026-10-26')->assertOk()->assertPageProp('reparations.total', 2);
        $response->assertOk()->assertPageProp('auth.permissions.users.manage', true)->assertPageProp('totalRepairs', 6)
            ->assertPageProp('totalClients', 1)->assertPageProp('todayDeposits', 2)->assertPageProp('weekTotal', 3)->assertPageProp('weekDifference', 1)
            ->assertPageProp('days', fn ($days) => count($days) === 7 && $days[0]['date'] === '2026-10-20' && $days[0]['count'] === 1 && $days[6]['count'] === 2)
            ->assertPageProp('statuses', fn ($statuses) => $statuses['en_attente']['count'] === 2 && $statuses['en_cours']['count'] === 1 && $statuses['termine']['count'] === 3)
            ->assertPageProp('recentRepairs', fn ($items) => count($items) === 5 && $items[0]['date_depot'] === '2026-10-26' && collect($items)->every(fn ($item) => array_key_exists('client', $item)));
    }

    public function test_terminal_only_data_shows_real_state_without_fake_week_activity(): void
    {
        $client = Client::create(['prenom' => 'Client', 'nom' => 'Test', 'telephone' => '70000000', 'piece_identite' => 'TEST']);
        Reparation::create(['client_id' => $client->id, 'marque' => 'Apple', 'modele' => 'iPhone', 'probleme' => 'Batterie', 'date_depot' => '2026-09-01', 'statut' => 'termine']);
        $this->actingAs(User::factory()->create(['role' => 'user']))->get('/dashboard')->assertOk()
            ->assertPageProp('weekTotal', 0)->assertPageProp('todayDeposits', 0)->assertPageProp('chartMax', 1)
            ->assertPageProp('statuses.termine.count', 1)->assertPageProp('recentRepairs.0.marque', 'Apple');
    }
}
