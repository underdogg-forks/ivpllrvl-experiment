<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Controllers\MailerController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * MailerController Feature Tests.
 *
 * Tests email configuration and testing display.
 */
#[CoversClass(MailerController::class)]
class MailerControllerTest extends FeatureTestCase
{
    /**
     * Test index displays mailer configuration page.
     */
    #[Test]
    public function it_displays_mailer_configuration_page(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('mailer.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('core::mailer_index');
    }
}
