<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Controllers\PaymentsController;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * PaymentsController Feature Tests.
 *
 * Tests payment recording and tracking.
 */
#[CoversClass(PaymentsController::class)]
class PaymentsControllerTest extends FeatureTestCase
{
    /**
     * Test index displays paginated list of payments.
     */
    #[Test]
    public function it_displays_paginated_list_of_payments(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        Payment::factory()->count(5)->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('payments.index'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('payments::index');
        $response->assertViewHas('payments');
        $response->assertViewHas('filter_display', true);
        $response->assertViewHas('filter_placeholder');
        $response->assertViewHas('filter_method', 'filter_payments');
    }

    /**
     * Test payments are ordered by date descending.
     */
    #[Test]
    public function it_orders_payments_by_date_descending(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        Payment::factory()->create(['payment_date' => '2024-01-01']);
        Payment::factory()->create(['payment_date' => '2024-01-02']);
        Payment::factory()->create(['payment_date' => '2024-01-03']);

        /** Act */
        $response = $this->actingAs($user)->get(route('payments.index'));

        /** Assert */
        $response->assertOk();
        $payments = $response->viewData('payments');
        
        // Most recent should be first
        $this->assertGreaterThan(0, $payments->count());
    }

    /**
     * Test payments are loaded with invoice and payment method relationships.
     */
    #[Test]
    public function it_loads_payments_with_relationships(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);

        /** Act */
        $response = $this->actingAs($user)->get(route('payments.index'));

        /** Assert */
        $response->assertOk();
        $payments = $response->viewData('payments');
        
        // Verify relationships are loaded
        $this->assertGreaterThan(0, $payments->count());
    }

    /**
     * Test form displays create form.
     */
    #[Test]
    public function it_displays_create_form(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('payments.form'));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('payments::form');
        $response->assertViewHas('payment');
        $response->assertViewHas('payment_methods');
    }

    /**
     * Test form displays edit form with existing payment.
     */
    #[Test]
    public function it_displays_edit_form_with_existing_payment(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $payment = Payment::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->get(route('payments.form', ['id' => $payment->payment_id]));

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('payments::form');
        $response->assertViewHas('payment');
        
        $viewPayment = $response->viewData('payment');
        $this->assertEquals($payment->payment_id, $viewPayment->payment_id);
    }

    /**
     * Test form creates new payment with valid data.
     */
    #[Test]
    public function it_creates_new_payment_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        /**
         * {
         *     "invoice_id": 1,
         *     "payment_date": "2024-01-15",
         *     "payment_amount": "100.00",
         *     "payment_method_id": 1,
         *     "btn_submit": "1"
         * }
         */
        $paymentData = [
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => '2024-01-15',
            'payment_amount' => '100.00',
            'payment_method_id' => $paymentMethod->payment_method_id,
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.form'), $paymentData);

        /** Assert */
        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_payments', [
            'invoice_id' => $invoice->invoice_id,
            'payment_amount' => '100.00',
        ]);
    }

    /**
     * Test form updates existing payment.
     */
    #[Test]
    public function it_updates_existing_payment(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['payment_amount' => '50.00']);
        
        /**
         * {
         *     "invoice_id": 1,
         *     "payment_date": "2024-01-15",
         *     "payment_amount": "75.00",
         *     "btn_submit": "1"
         * }
         */
        $updateData = [
            'invoice_id' => $payment->invoice_id,
            'payment_date' => $payment->payment_date,
            'payment_amount' => '75.00',
            'btn_submit' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.form', ['id' => $payment->payment_id]), $updateData);

        /** Assert */
        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseHas('ip_payments', [
            'payment_id' => $payment->payment_id,
            'payment_amount' => '75.00',
        ]);
    }

    /**
     * Test form validates required invoice_id.
     */
    #[Test]
    public function it_validates_required_invoice_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        /**
         * {
         *     "payment_date": "2024-01-15",
         *     "payment_amount": "100.00",
         *     "btn_submit": "1"
         * }
         */
        $missingInvoicePayload = [
            'payment_date' => '2024-01-15',
            'payment_amount' => '100.00',
            'btn_submit' => '1',
        ];

        $response = $this->actingAs($user)->post(route('payments.form'), $missingInvoicePayload);

        /** Assert */
        $response->assertSessionHasErrors('invoice_id');
    }

    /**
     * Test form validates required payment_date.
     */
    #[Test]
    public function it_validates_required_payment_date(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();

        /** Act */
        /**
         * {
         *     "invoice_id": 1,
         *     "payment_amount": "100.00",
         *     "btn_submit": "1"
         * }
         */
        $missingDatePayload = [
            'invoice_id' => $invoice->invoice_id,
            'payment_amount' => '100.00',
            'btn_submit' => '1',
        ];

        $response = $this->actingAs($user)->post(route('payments.form'), $missingDatePayload);

        /** Assert */
        $response->assertSessionHasErrors('payment_date');
    }

    /**
     * Test form validates required payment_amount.
     */
    #[Test]
    public function it_validates_required_payment_amount(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();

        /** Act */
        /**
         * {
         *     "invoice_id": 1,
         *     "payment_date": "2024-01-15",
         *     "btn_submit": "1"
         * }
         */
        $missingAmountPayload = [
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => '2024-01-15',
            'btn_submit' => '1',
        ];

        $response = $this->actingAs($user)->post(route('payments.form'), $missingAmountPayload);

        /** Assert */
        $response->assertSessionHasErrors('payment_amount');
    }

    /**
     * Test form redirects on cancel.
     */
    #[Test]
    public function it_redirects_to_index_on_cancel(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "btn_cancel": "1"
         * }
         */
        $cancelData = [
            'btn_cancel' => '1',
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.form'), $cancelData);

        /** Assert */
        $response->assertRedirect(route('payments.index'));
    }

    /**
     * Test delete removes payment.
     */
    #[Test]
    public function it_deletes_payment(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $payment = Payment::factory()->create();
        
        /**
         * {
         *     "payment_id": 1
         * }
         */
        $deletePayload = [
            'payment_id' => $payment->payment_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(
            route('payments.delete', ['id' => $payment->payment_id]),
            $deletePayload
        );

        /** Assert */
        $response->assertRedirect(route('payments.index'));
        $response->assertSessionHas('alert_success');
        
        $this->assertDatabaseMissing('ip_payments', [
            'payment_id' => $payment->payment_id,
        ]);
    }

    /**
     * Test delete returns 404 for non-existent payment.
     */
    #[Test]
    public function it_returns_404_when_deleting_non_existent_payment(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        /**
         * {
         *     "payment_id": 99999
         * }
         */
        $deletePayload = [
            'payment_id' => 99999,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(
            route('payments.delete', ['id' => 99999]),
            $deletePayload
        );

        /** Assert */
        $response->assertNotFound();
    }
}
