<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\SetupController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * SetupController Feature Tests.
 *
 * Tests initial setup wizard display.
 */
#[CoversClass(SetupController::class)]
class SetupControllerTest extends FeatureTestCase
{
    /**
     * Test index displays setup wizard page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_setup_wizard_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('setup.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup_index');
    }

    /**
     * Test setup wizard is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication for initial setup

        /** Act */
        $response = $this->get(route('setup.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::setup_index');
    }
}
