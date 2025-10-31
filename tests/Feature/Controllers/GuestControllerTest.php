<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Crm\Controllers\GuestController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * GuestController Feature Tests.
 *
 * Tests guest portal home page display.
 */
#[CoversClass(GuestController::class)]
class GuestControllerTest extends FeatureTestCase
{
    /**
     * Test index displays guest portal home page.
     */
    #[Test]
    public function it_displays_guest_portal_home_page(): void
    {
        /** Arrange */
        // Guest portal may not require authentication

        /** Act */
        $response = $this->get(route('guest.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_index');
    }

    /**
     * Test guest portal is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication

        /** Act */
        $response = $this->get(route('guest.index'));

        /** Assert */
        $response->assertOk();
    }

    /**
     * Test guest portal is also accessible when authenticated.
     */
    #[Test]
    public function it_is_accessible_when_authenticated(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('guest.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_index');
    }
}
