<?php

namespace Modules\Payments\Tests\Feature;

use Modules\Crm\Controllers\Gateways\PaypalController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
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
    #[Group('exotic')]
    #[Test]
    public function it_handles_paypal_ipn_notification(): void
    {
        /** Arrange */
        // PayPal IPN notifications require specific fields for validation
        // Note: Current implementation is a stub/TODO but test reflects real IPN data

        /** Act */
        /**
         * {
         *     "txn_id": "1234567890ABCDEF",
         *     "payment_status": "Completed",
         *     "mc_gross": "100.00",
         *     "mc_currency": "USD",
         *     "receiver_email": "merchant@example.com",
         *     "payer_email": "buyer@example.com",
         *     "custom": "invoice_123"
         * }
         */
        $payload = [
            'txn_id' => '1234567890ABCDEF',
            'payment_status' => 'Completed',
            'mc_gross' => '100.00',
            'mc_currency' => 'USD',
            'receiver_email' => 'merchant@example.com',
            'payer_email' => 'buyer@example.com',
            'custom' => 'invoice_123',
        ];

        $response = $this->post(route('gateways.paypal.notify'), $payload);

        /** Assert */
        // Note: Current stub implementation returns OK without validation
        // Future implementation should verify IPN signature, validate txn_id, update payment status
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
        // Note: Current implementation is a stub/TODO but test reflects real IPN data

        /** Act */
        /**
         * {
         *     "txn_id": "0987654321ZYXWVU",
         *     "payment_status": "Pending",
         *     "mc_gross": "50.00",
         *     "mc_currency": "EUR"
         * }
         */
        $payload = [
            'txn_id' => '0987654321ZYXWVU',
            'payment_status' => 'Pending',
            'mc_gross' => '50.00',
            'mc_currency' => 'EUR',
        ];

        $response = $this->post(route('gateways.paypal.notify'), $payload);

        /** Assert */
        // Note: Current stub implementation returns OK without validation
        // Future implementation should handle pending payments differently than completed
        $response->assertOk();
    }
}
