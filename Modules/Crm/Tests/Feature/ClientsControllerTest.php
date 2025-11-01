<?php

namespace Modules\Crm\Tests\Feature;

use Modules\Core\Models\User;
use Modules\Crm\Controllers\ClientsController;
use Modules\Crm\Models\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * ClientsController Feature Tests.
 *
 * Comprehensive test coverage for client management including CRUD operations,
 * status filtering, and relationship handling.
 */
#[CoversClass(ClientsController::class)]
class ClientsControllerTest extends FeatureTestCase
{
    /**
     * Test index redirects to active status view.
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_active_status_view_from_index(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.index'));

        /** Assert */
        $response->assertRedirect(route('clients.status', ['status' => 'active']));
    }

    /**
     * Test status method displays only active clients.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_only_active_clients_when_active_status_selected(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        $activeClient = Client::factory()->create(['client_active' => 1]);
        $inactiveClient = Client::factory()->create(['client_active' => 0]);

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.status', ['status' => 'active']));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::clients_index');
        $response->assertViewHas('records');
        
        $clients = $response->viewData('records');
        $clientIds = $clients->pluck('client_id')->toArray();
        $this->assertContains($activeClient->client_id, $clientIds);
        $this->assertNotContains($inactiveClient->client_id, $clientIds);
    }

    /**
     * Test status method displays only inactive clients.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_only_inactive_clients_when_inactive_status_selected(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        $activeClient = Client::factory()->create(['client_active' => 1]);
        $inactiveClient = Client::factory()->create(['client_active' => 0]);

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.status', ['status' => 'inactive']));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::clients_index');
        $response->assertViewHas('records');
        
        $clients = $response->viewData('records');
        $clientIds = $clients->pluck('client_id')->toArray();
        $this->assertNotContains($activeClient->client_id, $clientIds);
        $this->assertContains($inactiveClient->client_id, $clientIds);
    }

    /**
     * Test status method displays all clients.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_all_clients_when_all_status_selected(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        $activeClient = Client::factory()->create(['client_active' => 1]);
        $inactiveClient = Client::factory()->create(['client_active' => 0]);

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.status', ['status' => 'all']));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::clients_index');
        $response->assertViewHas('records');
        
        $clients = $response->viewData('records');
        $clientIds = $clients->pluck('client_id')->toArray();
        $this->assertContains($activeClient->client_id, $clientIds);
        $this->assertContains($inactiveClient->client_id, $clientIds);
    }

    /**
     * Test status view includes filter configuration.
     */
    #[Group('smoke')]
    #[Test]
    public function it_includes_filter_configuration_in_status_view(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.status', ['status' => 'active']));

        /** Assert */
        $response->assertOk();
        $response->assertViewHas('filter_display', true);
        $response->assertViewHas('filter_placeholder');
        $response->assertViewHas('filter_method', 'filter_clients');
    }

    /**
     * Test create displays client form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.create'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::clients_form');
        $response->assertViewHas('client');
        
        $client = $response->viewData('client');
        $this->assertInstanceOf(Client::class, $client);
        $this->assertFalse($client->exists);
    }

    /**
     * Test store creates new client with valid data.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_client_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "client_name": "Test Client Inc.",
         *     "client_email": "test@client.com",
         *     "client_active": 1
         * }
         */
        $clientData = [
            'client_name' => 'Test Client Inc.',
            'client_email' => 'test@client.com',
            'client_active' => 1,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('clients.store'), $clientData);

        /** Assert */
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_clients', [
            'client_name' => 'Test Client Inc.',
            'client_email' => 'test@client.com',
        ]);
    }

    /**
     * Test edit displays client form with existing data.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_edit_form_with_existing_client(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.edit', $client));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::clients_form');
        $response->assertViewHas('client');
        
        $viewClient = $response->viewData('client');
        $this->assertEquals($client->client_id, $viewClient->client_id);
    }

    /**
     * Test update modifies existing client.
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_client_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create([
            'client_name'  => 'Old Name',
            'client_email' => 'client@example.com',
            'client_active' => 0,
        ]);

        /**
         * {
         *     "client_name": "Updated Name",
         *     "client_email": "client@example.com",
         *     "client_active": 1
         * }
         */
        $updateData = [
            'client_name' => 'Updated Name',
            'client_email' => 'client@example.com',
            'client_active' => 1,
        ];

        /** Act */
        $response = $this->actingAs($user)->put(route('clients.update', $client), $updateData);

        /** Assert */
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_clients', [
            'client_id' => $client->client_id,
            'client_name' => 'Updated Name',
        ]);
    }

    /**
     * Test destroy deletes client.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_client(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->delete(route('clients.destroy', $client));

        /** Assert */
        $response->assertRedirect(route('clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_clients', [
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * Test clients are ordered alphabetically by name.
     */
    #[Test]
    public function it_orders_clients_alphabetically_by_name(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        Client::factory()->create(['client_name' => 'Zebra Company', 'client_active' => 1]);
        Client::factory()->create(['client_name' => 'Alpha Company', 'client_active' => 1]);
        Client::factory()->create(['client_name' => 'Beta Company', 'client_active' => 1]);

        /** Act */
        $response = $this->actingAs($user)->get(route('clients.status', ['status' => 'active']));

        /** Assert */
        $response->assertOk();
        $clients = $response->viewData('records');
        $names = $clients->pluck('client_name')->toArray();
        
        $this->assertEquals('Alpha Company', $names[0]);
        $this->assertEquals('Beta Company', $names[1]);
        $this->assertEquals('Zebra Company', $names[2]);
    }
}
