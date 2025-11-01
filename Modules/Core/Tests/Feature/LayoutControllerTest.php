<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\LayoutController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * LayoutController Feature Tests.
 *
 * Tests layout configuration display.
 */
#[CoversClass(LayoutController::class)]
class LayoutControllerTest extends FeatureTestCase
{
    /**
     * Test index displays layout configuration page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_layout_configuration_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('layout.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::layout_index');
    }
}
