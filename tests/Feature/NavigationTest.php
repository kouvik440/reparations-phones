<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_module_resolves_to_its_react_component_with_shared_permissions(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['/dashboard' => 'Dashboard', '/clients' => 'Clients/Index', '/reparations/find-client' => 'Repairs/FindClient', '/users' => 'Users/Index', '/roles/create' => 'Roles/Form', '/catalogue' => 'Catalog/Index'] as $url => $component) {
            $this->get($url)->assertOk()->assertPage($component)->assertPageProp('auth.permissions.repairs.view', true);
        }
    }
}
