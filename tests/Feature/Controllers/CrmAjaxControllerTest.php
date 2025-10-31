<?php

namespace Tests\Feature\Controllers;

use Modules\Crm\Controllers\AjaxController as CrmAjaxController;
use Modules\Crm\Models\Client;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * CRM AjaxController Feature Tests.
 *
 * Tests AJAX requests for CRM operations.
 */
#[CoversClass(CrmAjaxController::class)]
class CrmAjaxControllerTest extends FeatureTestCase
{
    /**
     * Test modalClientLookup displays active clients.
     */
    #[Test]
    public function it_displays_modal_with_active_clients(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        $activeClient = Client::factory()->create(['client_active' => 1, 'client_name' => 'Active Client']);
        $inactiveClient = Client::factory()->create(['client_active' => 0, 'client_name' => 'Inactive Client']);

        /** Act */
        $response = $this->actingAs($user)->get(route('crm.ajax.modal_client_lookup'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::modal_client_lookup');
        $response->assertViewHas('clients');
        
        $clients = $response->viewData('clients');
        $clientIds = $clients->pluck('client_id')->toArray();
        
        $this->assertContains($activeClient->client_id, $clientIds);
        $this->assertNotContains($inactiveClient->client_id, $clientIds);
    }

    /**
     * Test clients are ordered alphabetically by name.
     */
    #[Test]
    public function it_orders_clients_alphabetically_in_modal(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        Client::factory()->create(['client_active' => 1, 'client_name' => 'Zebra Corp']);
        Client::factory()->create(['client_active' => 1, 'client_name' => 'Alpha Inc']);
        Client::factory()->create(['client_active' => 1, 'client_name' => 'Beta LLC']);

        /** Act */
        $response = $this->actingAs($user)->get(route('crm.ajax.modal_client_lookup'));

        /** Assert */
        $clients = $response->viewData('clients');
        $names = $clients->pluck('client_name')->toArray();
        
        $this->assertEquals('Alpha Inc', $names[0]);
        $this->assertEquals('Beta LLC', $names[1]);
        $this->assertEquals('Zebra Corp', $names[2]);
    }

    /**
     * Test getClientDetails returns client as JSON.
     */
    #[Test]
    public function it_returns_client_details_as_json(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'client_name' => 'Test Client',
            'client_email' => 'test@client.com',
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('crm.ajax.get_client_details', ['clientId' => $client->client_id]));

        /** Assert */
        $response->assertOk();
        $response->assertJson([
            'client_id' => $client->client_id,
            'client_name' => 'Test Client',
            'client_email' => 'test@client.com',
        ]);
    }

    /**
     * Test getClientDetails returns 404 for non-existent client.
     */
    #[Test]
    public function it_returns_404_for_non_existent_client(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('crm.ajax.get_client_details', ['clientId' => 99999]));

        /** Assert */
        $response->assertNotFound();
    }
}
