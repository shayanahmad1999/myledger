<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FinanceSetupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebFinanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_open_registration_page(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your workspace');
    }

    public function test_authenticated_user_can_open_dashboard_and_ajax_data(): void
    {
        $user = User::factory()->create();
        app(FinanceSetupService::class)->bootstrapUser($user);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee('Dashboard');

        $this->actingAs($user)
            ->getJson('/ajax/dashboard')
            ->assertOk()
            ->assertJsonStructure(['net_worth', 'month', 'accounts', 'loans', 'budgets']);
    }

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
