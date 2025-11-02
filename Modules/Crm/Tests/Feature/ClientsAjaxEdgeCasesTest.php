<?php

namespace Modules\Crm\Tests\Feature;

use Modules\Crm\Controllers\AjaxController as CrmAjaxController;
use Modules\Crm\Models\Client;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * CRM AjaxController Edge Cases Tests.
 *
 * Focused test suite for edge cases and validation scenarios.
 */
#[CoversClass(CrmAjaxController::class)]
class ClientsAjaxEdgeCasesTest extends FeatureTestCase
{
    // ==================== VALIDATION & EDGE CASES ====================

    /**
     * Test getClientDetails with invalid ID type.
     */
    #[Group('validation')]
    #[Test]
    public function it_handles_invalid_client_id_type(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('crm.ajax.get_client_details', ['clientId' => 'invalid']));

        /** Assert */
        // Should either return 404 or handle gracefully
        $this->assertTrue(
            $response->isNotFound() || 
            $response->getStatusCode() >= 400
        );
    }

    /**
     * Test getClientDetails with negative ID.
     */
    #[Group('validation')]
    #[Test]
    public function it_handles_negative_client_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('crm.ajax.get_client_details', ['clientId' => -1]));

        /** Assert */
        $response->assertNotFound();
    }

    /**
     * Test getClientDetails with zero ID.
     */
    #[Group('validation')]
    #[Test]
    public function it_handles_zero_client_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('crm.ajax.get_client_details', ['clientId' => 0]));

        /** Assert */
        $response->assertNotFound();
    }
}
