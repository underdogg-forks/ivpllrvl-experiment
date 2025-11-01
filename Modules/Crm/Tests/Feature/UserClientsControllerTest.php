<?php

namespace Modules\Crm\Tests\Feature;

use Modules\Core\Models\User;
use Modules\Crm\Controllers\UserClientsController;
use Modules\Crm\Models\Client;
use Modules\Crm\Models\UserClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * UserClientsController Feature Tests.
 *
 * Tests user-client relationship management.
 */
#[CoversClass(UserClientsController::class)]
class UserClientsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of user-client relationships.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_paginated_list_of_user_clients(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();
        UserClient::factory()->count(5)->create([
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('user_clients.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::user_clients_index');
        $response->assertViewHas('user_clients');
    }

    /**
     * Test index loads user and client relationships.
     */
    #[Group('smoke')]
    #[Test]
    public function it_loads_user_and_client_relationships(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();
        UserClient::factory()->create([
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('user_clients.index'));

        /** Assert */
        $response->assertOk();
        $userClients = $response->viewData('user_clients');
        
        // Verify relationships are loaded
        $this->assertGreaterThan(0, $userClients->count());
    }

    /**
     * Test form displays create form.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('user_clients.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::user_clients_form');
        $response->assertViewHas('user_client');
        
        $userClient = $response->viewData('user_client');
        $this->assertInstanceOf(UserClient::class, $userClient);
        $this->assertFalse($userClient->exists);
    }

    /**
     * Test form displays edit form with existing user-client.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_edit_form_with_existing_user_client(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $userClient = UserClient::factory()->create([
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('user_clients.form', ['id' => $userClient->id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::user_clients_form');
        $response->assertViewHas('user_client');
        
        $viewUserClient = $response->viewData('user_client');
        $this->assertEquals($userClient->id, $viewUserClient->id);
    }

    /**
     * Test form creates new user-client relationship.
     */
    #[Group('crud')]
    #[Test]
    public function it_creates_new_user_client_relationship_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();
        
        /**
         * {
         *     "user_id": 1,
         *     "client_id": 1,
         *     "btn_submit": "1"
         * }
         */
        $userClientData = [
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('user_clients.form'), $userClientData);

        /** Assert */
        $response->assertRedirect(route('user_clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_user_clients', [
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
        ]);
    }

    /**
     * Test form updates existing user-client relationship.
     */
    #[Group('crud')]
    #[Test]
    public function it_updates_existing_user_client_relationship(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client1 = Client::factory()->create();
        $client2 = Client::factory()->create();
        
        $userClient = UserClient::factory()->create([
            'user_id' => $user->user_id,
            'client_id' => $client1->client_id,
        ]);
        
        /**
         * {
         *     "user_id": 1,
         *     "client_id": 1,
         *     "btn_submit": "1"
         * }
         */
        $updateData = [
            'user_id' => $user->user_id,
            'client_id' => $client2->client_id,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('user_clients.form', ['id' => $userClient->id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('user_clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_user_clients', [
            'id' => $userClient->id,
            'client_id' => $client2->client_id,
        ]);
    }

    /**
     * Test form validates required user_id.
     */
    #[Test]
    public function it_validates_required_user_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();

        /** Act */
        /**
         * {
         *     "client_id": 1,
         *     "btn_submit": "1"
         * }
         */
        $missingUserPayload = [
            'client_id' => $client->client_id,
            'btn_submit' => '1',
        ];

        $response = $this->actingAs($user)->post(route('user_clients.form'), $missingUserPayload);

        /** Assert */
        $response->assertSessionHasErrors('user_id');
    }

    /**
     * Test form validates required client_id.
     */
    #[Test]
    public function it_validates_required_client_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        /**
         * {
         *     "user_id": 1,
         *     "btn_submit": "1"
         * }
         */
        $missingClientPayload = [
            'user_id' => $user->user_id,
            'btn_submit' => '1',
        ];

        $response = $this->actingAs($user)->post(route('user_clients.form'), $missingClientPayload);

        /** Assert */
        $response->assertSessionHasErrors('client_id');
    }

    /**
     * Test form redirects on cancel.
     */
    #[Group('smoke')]
    #[Test]
    public function it_redirects_to_index_on_cancel(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "btn_cancel": "1"
         * }
         */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('user_clients.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('user_clients.index'));
    }

    /**
     * Test delete removes user-client relationship.
     */
    #[Group('crud')]
    #[Test]
    public function it_deletes_user_client_relationship(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $userClient = UserClient::factory()->create([
            'user_id' => $user->user_id,
            'client_id' => $client->client_id,
        ]);
        
        /**
         * {
         *     "user_client_id": 1
         * }
         */
        $deletePayload = [
            'user_client_id' => $userClient->user_client_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(
            route('user_clients.delete', ['id' => $userClient->user_client_id]),
            $deletePayload
        );

        /** Assert */
        $response->assertRedirect(route('user_clients.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_user_clients', [
            'user_client_id' => $userClient->user_client_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent user-client.
     */
    #[Group('smoke')]
    #[Test]
    public function it_returns_404_when_deleting_non_existent_user_client(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "user_client_id": 99999
         * }
         */
        $deletePayload = [
            'user_client_id' => 99999,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(
            route('user_clients.delete', ['id' => 99999]),
            $deletePayload
        );

        /** Assert */
        $response->assertNotFound();
    }
}
