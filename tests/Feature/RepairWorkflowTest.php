<?php

namespace Tests\Feature;

use App\Actions\Reparations\ChangeRepairStatus;
use App\Enums\RepairStatus;
use App\Models\Client;
use App\Models\RepairStatusChange;
use App\Models\Reparation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RepairWorkflowTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Concerns\CreatesDeviceCatalog;

    private function repair(): Reparation
    {
        $client = Client::create(['prenom' => 'Client', 'nom' => 'Workflow', 'telephone' => '70000000', 'piece_identite' => 'PRIVE']);

        return Reparation::create(['client_id' => $client->id, 'marque' => 'Samsung', 'modele' => 'A10', 'probleme' => 'Panne', 'date_depot' => today()->toDateString(), 'statut' => 'en_attente']);
    }

    public function test_forward_transitions_record_actor_time_and_history_and_delivered_is_final(): void
    {
        $actor = User::factory()->create(['name' => 'Technicien Atelier']);
        $repair = $this->repair();
        $this->actingAs($actor);
        foreach (['en_cours', 'termine', 'restitue'] as $version => $status) {
            $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => $status, 'status_version' => $version, 'user_id' => 999, 'actor_name' => 'Faux auteur'])
                ->assertOk()->assertJsonPath('statut', $status)->assertJsonPath('status_version', $version + 1);
            $change = $repair->statusChanges()->first();
            $this->assertSame($actor->id, $change->user_id);
            $this->assertSame('Technicien Atelier', $change->actor_name);
            $this->assertSame($status, $change->to_status);
            $this->assertNotNull($change->created_at);
        }
        $this->assertSame('en_attente', $repair->statusChanges()->where('version', 1)->first()->from_status);
        $this->assertSame('termine', $repair->statusChanges()->first()->from_status);
        $this->get('/reparations/'.$repair->id)->assertOk()->assertPage('Repairs/Show')->assertPageProp('history.data.0.actor_name', 'Technicien Atelier')->assertPageProp('reparation.statut', 'restitue');
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'en_cours', 'status_version' => 3])->assertUnprocessable();
        $this->assertDatabaseCount('repair_status_changes', 3);
    }

    public function test_skipped_reverse_and_unknown_transitions_are_rejected_without_audit_or_mutation(): void
    {
        $repair = $this->repair();
        $this->actingAs(User::factory()->create());
        foreach (['termine', 'restitue', 'inconnu'] as $status) {
            $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => $status, 'status_version' => 0])->assertUnprocessable();
        }
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'en_cours'])->assertUnprocessable();
        $this->assertSame('en_attente', $repair->fresh()->statut);
        $this->assertDatabaseCount('repair_status_changes', 0);
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'en_attente', 'status_version' => 0])->assertOk();
        $this->assertDatabaseCount('repair_status_changes', 0);
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'en_cours', 'status_version' => 0])->assertOk();
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'en_attente', 'status_version' => 1])->assertUnprocessable();
        $this->assertSame('en_cours', $repair->fresh()->statut);
        $this->assertDatabaseCount('repair_status_changes', 1);
    }

    public function test_stale_or_duplicate_request_cannot_overwrite_a_change(): void
    {
        $repair = $this->repair();
        $this->actingAs(User::factory()->create());
        $url = '/reparations/'.$repair->id.'/statut';
        $this->putJson($url, ['statut' => 'en_cours', 'status_version' => 0])->assertOk();
        $this->putJson($url, ['statut' => 'termine', 'status_version' => 0])->assertConflict();
        $this->putJson($url, ['statut' => 'en_cours', 'status_version' => 0])->assertConflict();
        $this->assertSame('en_cours', $repair->fresh()->statut);
        $this->assertDatabaseCount('repair_status_changes', 1);
    }

    public function test_audit_failure_rolls_back_status_and_version(): void
    {
        $repair = $this->repair();
        $actor = User::factory()->create();
        $event = 'eloquent.creating: '.RepairStatusChange::class;
        Event::listen($event, fn () => throw new \RuntimeException('Simulated audit failure'));
        try {
            app(ChangeRepairStatus::class)->handle($repair->id, RepairStatus::Working, 0, $actor);
            $this->fail('Audit failure should prevent status change.');
        } catch (\RuntimeException $error) {
            $this->assertSame('Simulated audit failure', $error->getMessage());
        } finally {
            Event::forget($event);
        }
        $this->assertSame('en_attente', $repair->fresh()->statut);
        $this->assertSame(0, $repair->fresh()->status_version);
        $this->assertDatabaseCount('repair_status_changes', 0);
    }

    public function test_new_deposit_records_initial_event_but_legacy_history_is_not_invented(): void
    {
        $legacy = $this->repair();
        $actor = User::factory()->create();
        $this->actingAs($actor)->get('/reparations/'.$legacy->id)->assertPageProp('history.total', 0);
        $this->post('/reparations', [...$this->deviceSelection('Apple', '13'), 'client_id' => $legacy->client_id, 'marque' => 'Apple', 'modele' => '13', 'probleme' => 'Écran', 'date_depot' => today()->toDateString()])->assertRedirect();
        $this->assertDatabaseHas('repair_status_changes', ['user_id' => $actor->id, 'from_status' => null, 'to_status' => 'en_attente', 'version' => 0]);
        $this->assertDatabaseCount('repair_status_changes', 1);
    }

    public function test_read_only_cannot_transition_and_public_tracking_never_shows_audit_authors(): void
    {
        $repair = $this->repair();
        $actor = User::factory()->create(['name' => 'AuteurConfidentiel']);
        foreach ([RepairStatus::Working, RepairStatus::Completed, RepairStatus::Delivered] as $version => $target) {
            app(ChangeRepairStatus::class)->handle($repair->id, $target, $version, $actor);
        }
        $role = Role::forceCreate(['name' => 'Lecture', 'slug' => 'lecture', 'permissions' => ['repairs.view'], 'is_system' => false]);
        $this->actingAs(User::factory()->create(['role' => $role->slug]));
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'termine', 'status_version' => 3])->assertForbidden();
        $this->post('/logout');
        $this->putJson('/reparations/'.$repair->id.'/statut', ['statut' => 'termine', 'status_version' => 3])->assertUnauthorized();
        $this->post('/suivi', ['code' => $repair->code])->assertOk()->assertPageProp('reparation.statut', 'restitue')->assertDontSee('AuteurConfidentiel')->assertDontSee('PRIVE');
        $this->actingAs($actor)->get('/dashboard')->assertOk()->assertPageProp('statuses.termine.count', 0)->assertPageProp('statuses.restitue.count', 1);
        $this->get('/reparations?statut=restitue')->assertOk()->assertPageProp('reparations.total', 1);
        $this->get('/clients/'.$repair->client_id)->assertOk()->assertPageProp('reparations.data.0.statut', 'restitue');
    }
}
