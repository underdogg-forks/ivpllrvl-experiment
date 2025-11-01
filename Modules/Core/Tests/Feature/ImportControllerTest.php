<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\ImportController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

/**
 * ImportController Feature Tests.
 *
 * Tests data import functionality display.
 */
#[CoversClass(ImportController::class)]
class ImportControllerTest extends FeatureTestCase
{
    /**
     * Test index displays import page.
     */
    #[Group('smoke')]
    #[Test]
    public function it_displays_import_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('import.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::import_index');
    }
}
