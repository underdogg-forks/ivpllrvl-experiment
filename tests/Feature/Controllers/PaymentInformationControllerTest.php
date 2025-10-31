<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Crm\Controllers\PaymentInformationController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * PaymentInformationController Feature Tests.
 *
 * Tests guest payment information display.
 */
#[CoversClass(PaymentInformationController::class)]
class PaymentInformationControllerTest extends FeatureTestCase
{
    /**
     * Test index displays payment information page.
     */
    #[Test]
    public function it_displays_payment_information_page(): void
    {
        /** Arrange */
        // Payment info may be accessible to guests

        /** Act */
        $response = $this->get(route('payment_information.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_payment_info');
    }

    /**
     * Test payment information is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication required

        /** Act */
        $response = $this->get(route('payment_information.index'));

        /** Assert */
        $response->assertOk();
    }

    /**
     * Test payment information is also accessible when authenticated.
     */
    #[Test]
    public function it_is_accessible_when_authenticated(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('payment_information.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_payment_info');
    }
}
