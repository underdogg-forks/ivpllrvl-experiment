<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\VersionsController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * VersionsController Feature Tests.
 *
 * Tests version information and update checking.
 */
#[CoversClass(VersionsController::class)]
class VersionsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays versions page.
     */
    #[Test]
    public function it_displays_versions_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('versions.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::versions_index');
    }
}
