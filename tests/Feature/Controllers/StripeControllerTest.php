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
        // Stripe webhooks require event type and data object
        // Note: Current implementation is a stub/TODO but test reflects real webhook data

        /** Act */
        /**
         * {
         *     "id": "evt_1234567890",
         *     "type": "payment_intent.succeeded",
         *     "data": {
         *         "object": {
         *             "id": "pi_1234567890",
         *             "amount": 10000,
         *             "currency": "usd",
         *             "status": "succeeded",
         *             "metadata": {
         *                 "invoice_id": "123"
         *             }
         *         }
         *     }
         * }
         */
        $payload = [
            'id' => 'evt_1234567890',
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_1234567890',
                    'amount' => 10000,
                    'currency' => 'usd',
                    'status' => 'succeeded',
                    'metadata' => [
                        'invoice_id' => '123',
                    ],
                ],
            ],
        ];

        $response = $this->post(route('gateways.stripe.notify'), $payload);

        /** Assert */
        // Note: Current stub implementation returns OK without validation
        // Future implementation should verify webhook signature, handle event types, update payment records
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
        // Note: Current implementation is a stub/TODO but test reflects real webhook data

        /** Act */
        /**
         * {
         *     "id": "evt_0987654321",
         *     "type": "charge.refunded",
         *     "data": {
         *         "object": {
         *             "id": "ch_0987654321",
         *             "amount": 5000,
         *             "refunded": true
         *         }
         *     }
         * }
         */
        $payload = [
            'id' => 'evt_0987654321',
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_0987654321',
                    'amount' => 5000,
                    'refunded' => true,
                ],
            ],
        ];

        $response = $this->post(route('gateways.stripe.notify'), $payload);

        /** Assert */
        // Note: Current stub implementation returns OK without validation
        // Future implementation should handle different event types (refunds, disputes, etc.)
        $response->assertOk();
    }
}
