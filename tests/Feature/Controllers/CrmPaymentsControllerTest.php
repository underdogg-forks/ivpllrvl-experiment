<?php

namespace Tests\Feature\Controllers;

use Modules\Crm\Controllers\PaymentsController as GuestPaymentsController;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * PaymentsController (CRM/Guest) Feature Tests.
 *
 * Tests guest portal payment submission.
 */
#[CoversClass(GuestPaymentsController::class)]
class CrmPaymentsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays guest payment form.
     */
    #[Test]
    public function it_displays_guest_payment_form(): void
    {
        /** Arrange */
        // Guest portal accessible without authentication

        /** Act */
        $response = $this->get(route('guest.payments'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_payments');
    }

    /**
     * Test payment form is accessible without authentication.
     */
    #[Test]
    public function it_is_accessible_without_authentication(): void
    {
        /** Arrange */
        // No authentication required

        /** Act */
        $response = $this->get(route('guest.payments'));

        /** Assert */
        $response->assertOk();
    }

    /**
     * Test submit redirects with success message.
     */
    #[Test]
    public function it_submits_payment_and_redirects_with_success(): void
    {
        /** Arrange */
        // Guest payment submission requires invoice URL key and payment details
        // Note: Current implementation is a stub/TODO but test reflects real-world data

        /** Act */
        /**
         * {
         *     "invoice_url_key": "abc123def456",
         *     "payment_method": "paypal",
         *     "amount": "100.00",
         *     "payment_status": "completed"
         * }
         */
        $payload = [
            'invoice_url_key' => 'abc123def456',
            'payment_method' => 'paypal',
            'amount' => '100.00',
            'payment_status' => 'completed',
        ];

        $response = $this->post(route('guest.payments.submit'), $payload);

        /** Assert */
        // Note: Current stub implementation ignores payload and always succeeds
        // Future implementation should validate these fields
        $response->assertRedirect();
        $response->assertSessionHas('alert_success');
    }

    /**
     * Test payment submission is accessible without authentication.
     */
    #[Test]
    public function it_allows_payment_submission_without_authentication(): void
    {
        /** Arrange */
        // No authentication required for guest payments
        // Note: Current implementation is a stub/TODO but test reflects real-world data

        /** Act */
        /**
         * {
         *     "invoice_url_key": "xyz789ghi012",
         *     "payment_method": "stripe",
         *     "amount": "250.50"
         * }
         */
        $payload = [
            'invoice_url_key' => 'xyz789ghi012',
            'payment_method' => 'stripe',
            'amount' => '250.50',
        ];

        $response = $this->post(route('guest.payments.submit'), $payload);

        /** Assert */
        // Note: Current stub implementation ignores payload and always succeeds
        // Future implementation should validate invoice_url_key exists, amount is positive, etc.
        $response->assertRedirect();
        $response->assertSessionHas('alert_success');
    }

    /**
     * Test payment operations work when authenticated.
     */
    #[Test]
    public function it_works_when_authenticated(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('guest.payments'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('crm::guest_payments');
    }
}
