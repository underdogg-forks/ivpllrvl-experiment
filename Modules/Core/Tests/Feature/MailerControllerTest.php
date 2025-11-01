<?php

namespace Modules\Core\Tests\Feature;

use Modules\Core\Controllers\MailerController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
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
    #[Group('smoke')]
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
