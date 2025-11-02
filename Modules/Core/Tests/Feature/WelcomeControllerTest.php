<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\WelcomeController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * WelcomeController Feature Tests.
 *
 * Tests welcome/landing page display.
 */
#[CoversClass(WelcomeController::class)]
class WelcomeControllerTest extends FeatureTestCase
{
    /**
     * Test index displays welcome page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_welcome_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $this->actingAs($user);
        $response = $this->get(route('welcome'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::welcome');
    }

    /**
     * Test welcome page is accessible without authentication.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_welcome_page_without_authentication(): void
    {
        /** Arrange */
        // No user authentication

        /** Act */
        $response = $this->get(route('welcome'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::welcome');
    }
}
