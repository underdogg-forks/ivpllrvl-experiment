<?php

namespace Modules\Crm\Tests\Feature;

use Modules\Core\Models\User;
use Modules\Crm\Controllers\GetController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * GetController Feature Tests.
 *
 * Tests guest get/download operations.
 */
#[CoversClass(GetController::class)]
class GetControllerTest extends FeatureTestCase
{
    /**
     * Test index displays guest get page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_guest_get_page(): void
    {
        /** Arrange */
        // Guest operations may not require authentication

        /** Act */
        $response = $this->get(route('guest.get'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_get');
    }

    /**
     * Test guest get page is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication required

        /** Act */
        $response = $this->get(route('guest.get'));

        /** Assert */
        $response->assertOk();
    }

    /**
     * Test guest get page is also accessible when authenticated.
     */
    #[Test]
    public function it_is_accessible_when_authenticated(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('guest.get'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_get');
    }
}
