<?php

namespace Tests\Feature\Controllers;

use Modules\Crm\Controllers\Gateways\PaypalController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * PaypalController Feature Tests.
 *
 * Tests PayPal payment gateway integration.
 */
#[CoversClass(PaypalController::class)]
class PaypalControllerTest extends FeatureTestCase
{
    /**
     * Test notify handles PayPal IPN notification.
     */
    #[Test]
    public function it_handles_paypal_ipn_notification(): void
    {
        /** Arrange */
        // PayPal IPN notifications are external webhooks

        /** Act */
        $response = $this->post(route('gateways.paypal.notify'));

        /** Assert */
        $response->assertOk();
        $this->assertEquals('OK', $response->getContent());
    }

    /**
     * Test notify is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // Webhook endpoints should not require authentication

        /** Act */
        $response = $this->post(route('gateways.paypal.notify'));

        /** Assert */
        $response->assertOk();
    }
}
