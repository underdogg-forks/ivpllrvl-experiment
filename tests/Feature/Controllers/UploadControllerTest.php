<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\UploadController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * UploadController Feature Tests.
 *
 * Tests file upload handling display.
 */
#[CoversClass(UploadController::class)]
class UploadControllerTest extends FeatureTestCase
{
    /**
     * Test index displays upload page.
     */
    #[Test]
    public function it_displays_upload_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('upload.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::upload_index');
    }
}
