<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\ReportsController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * ReportsController Feature Tests.
 *
 * Tests financial reports and analytics display.
 */
#[CoversClass(ReportsController::class)]
class ReportsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays reports page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_reports_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /* Act */
        $this->actingAs($user);
        $response = $this->get(route('reports.index'));

        /* Assert */
        $response->assertOk();
        $response->assertViewIs('core::reports_index');
    }
}
