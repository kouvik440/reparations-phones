<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_tracking_are_public_react_pages(): void
    {
        $this->get('/')->assertOk()->assertPage('Public/Home')->assertPageProp('auth.user', null);
        $this->get('/login')->assertOk()->assertPage('Auth/Login');
        $this->get('/suivi')->assertOk()->assertPage('Public/Tracking')->assertPageProp('searched', false);
        $this->get('/users')->assertRedirect('/login');
    }

    public function test_home_does_not_redirect_authenticated_visitors(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/')->assertOk()->assertPage('Public/Home');
    }

    public function test_configured_content_is_passed_to_react_as_plain_data(): void
    {
        config()->set('workshop.history.paragraphs', ['Une histoire confirmée <script>alert(1)</script>']);
        config()->set('workshop.contact.address', 'Adresse validée');
        $this->get('/')->assertPageProp('workshop.history.paragraphs.0', 'Une histoire confirmée <script>alert(1)</script>')
            ->assertPageProp('workshop.contact.address', 'Adresse validée')->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_empty_content_remains_a_valid_react_page(): void
    {
        config()->set('workshop', []);
        $this->get('/')->assertOk()->assertPage('Public/Home')->assertPageProp('workshop', []);
    }
}
