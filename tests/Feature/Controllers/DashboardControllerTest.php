<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\DashboardController;
use Modules\Core\Models\User;
use Modules\Crm\Models\Client;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * DashboardController Feature Tests.
 *
 * Tests dashboard display with statistics and overview data.
 */
#[CoversClass(DashboardController::class)]
class DashboardControllerTest extends FeatureTestCase
{
    /**
     * Test index displays dashboard with statistics.
     */
    #[Test]
    public function it_displays_dashboard_with_statistics(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        // Create test data
        Client::factory()->count(5)->create();
        Invoice::factory()->count(10)->create();
        Quote::factory()->count(3)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('dashboard'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::dashboard');
        $response->assertViewHas('total_clients');
        $response->assertViewHas('total_invoices');
        $response->assertViewHas('total_quotes');
    }

    /**
     * Test index shows correct client count.
     */
    #[Test]
    public function it_shows_correct_client_count(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Client::factory()->count(7)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('dashboard'));

        /** Assert */
        $response->assertOk();
        $totalClients = $response->viewData('total_clients');
        $this->assertEquals(7, $totalClients);
    }

    /**
     * Test index shows correct invoice count.
     */
    #[Test]
    public function it_shows_correct_invoice_count(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Invoice::factory()->count(15)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('dashboard'));

        /** Assert */
        $response->assertOk();
        $totalInvoices = $response->viewData('total_invoices');
        $this->assertEquals(15, $totalInvoices);
    }

    /**
     * Test index shows correct quote count.
     */
    #[Test]
    public function it_shows_correct_quote_count(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Quote::factory()->count(8)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('dashboard'));

        /** Assert */
        $response->assertOk();
        $totalQuotes = $response->viewData('total_quotes');
        $this->assertEquals(8, $totalQuotes);
    }

    /**
     * Test index shows zero counts when no data exists.
     */
    #[Test]
    public function it_shows_zero_counts_when_no_data_exists(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('dashboard'));

        /** Assert */
        $response->assertOk();
        $this->assertEquals(0, $response->viewData('total_clients'));
        $this->assertEquals(0, $response->viewData('total_invoices'));
        $this->assertEquals(0, $response->viewData('total_quotes'));
    }
}
