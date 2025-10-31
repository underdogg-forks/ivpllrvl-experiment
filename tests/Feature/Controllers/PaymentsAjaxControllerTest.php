<?php

namespace Tests\Feature\Controllers;

use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Controllers\AjaxController as PaymentsAjaxController;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\FeatureTestCase;

/**
 * Payments AjaxController Feature Tests.
 *
 * Tests AJAX requests for payment operations.
 */
#[CoversClass(PaymentsAjaxController::class)]
class PaymentsAjaxControllerTest extends FeatureTestCase
{
    /**
     * Test add creates payment via AJAX with valid data.
     */
    #[Test]
    public function it_creates_payment_via_ajax_with_valid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        /** @var array{invoice_id: int, payment_date: string, payment_amount: string, payment_method_id: int} $paymentData */
        $paymentData = [
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => '2024-01-15',
            'payment_amount' => '100.00',
            'payment_method_id' => $paymentMethod->payment_method_id,
        ];

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.add'), $paymentData);

        /** Assert */
        $response->assertOk();
        $response->assertJson(['success' => 1]);
        $response->assertJsonStructure(['success', 'payment_id']);
        
        $this->assertDatabaseHas('ip_payments', [
            'invoice_id' => $invoice->invoice_id,
            'payment_amount' => '100.00',
        ]);
    }

    /**
     * Test add returns validation errors with invalid data.
     */
    #[Test]
    public function it_returns_validation_errors_with_invalid_data(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.add'), [
            'payment_date' => 'invalid-date',
            'payment_amount' => 'not-a-number',
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertJson(['success' => 0]);
        $response->assertJsonStructure(['success', 'validation_errors']);
    }

    /**
     * Test add validates required invoice_id.
     */
    #[Test]
    public function it_validates_required_invoice_id(): void
    {
        /** Arrange */
        $user = User::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.add'), [
            'payment_date' => '2024-01-15',
            'payment_amount' => '100.00',
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertJson(['success' => 0]);
        
        $data = $response->json();
        $this->assertArrayHasKey('validation_errors', $data);
        $this->assertArrayHasKey('invoice_id', $data['validation_errors']);
    }

    /**
     * Test add validates required payment_date.
     */
    #[Test]
    public function it_validates_required_payment_date(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.add'), [
            'invoice_id' => $invoice->invoice_id,
            'payment_amount' => '100.00',
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertJson(['success' => 0]);
        
        $data = $response->json();
        $this->assertArrayHasKey('payment_date', $data['validation_errors']);
    }

    /**
     * Test add validates required payment_amount.
     */
    #[Test]
    public function it_validates_required_payment_amount(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.add'), [
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => '2024-01-15',
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertJson(['success' => 0]);
        
        $data = $response->json();
        $this->assertArrayHasKey('payment_amount', $data['validation_errors']);
    }

    /**
     * Test modal_add_payment displays modal view.
     */
    #[Test]
    public function it_displays_modal_add_payment_view(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        PaymentMethod::factory()->count(3)->create();

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.modal_add_payment'), [
            'invoice_id' => 1,
            'invoice_balance' => '100.00',
            'invoice_payment_method' => 1,
        ]);

        /** Assert */
        $response->assertOk();
        $response->assertViewIs('payments::modal_add_payment');
        $response->assertViewHas('payment_methods');
        $response->assertViewHas('invoice_id', 1);
        $response->assertViewHas('invoice_balance', '100.00');
    }

    /**
     * Test modal includes all payment methods ordered.
     */
    #[Test]
    public function it_includes_all_payment_methods_in_modal(): void
    {
        /** Arrange */
        $user = User::factory()->create();
        
        PaymentMethod::factory()->create(['payment_method_name' => 'Cash']);
        PaymentMethod::factory()->create(['payment_method_name' => 'Check']);
        PaymentMethod::factory()->create(['payment_method_name' => 'Credit Card']);

        /** Act */
        $response = $this->actingAs($user)->post(route('payments.ajax.modal_add_payment'));

        /** Assert */
        $response->assertOk();
        $paymentMethods = $response->viewData('payment_methods');
        
        $this->assertCount(3, $paymentMethods);
    }
}
