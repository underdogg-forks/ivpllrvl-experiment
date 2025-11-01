<?php

namespace Tests\Feature\Controllers;

use Modules\Crm\Controllers\Gateways\StripeController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * StripeController Feature Tests.
 *
 * Tests Stripe payment gateway integration.
 */
#[CoversClass(StripeController::class)]
class StripeControllerTest extends FeatureTestCase
{
    /**
     * Test notify handles Stripe webhook notification.
     */
    #[Test]
    public function it_handles_stripe_webhook_notification(): void
    {
        /** Arrange */
        // Stripe webhooks are external notifications

        /** Act */
        /**
         * {}
         */
        $payload = [];

        $response = $this->post(route('gateways.stripe.notify'), $payload);

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
        /**
         * {}
         */
        $payload = [];

        $response = $this->post(route('gateways.stripe.notify'), $payload);

        /** Assert */
        $response->assertOk();
    }
}
