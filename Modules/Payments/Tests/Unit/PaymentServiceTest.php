<?php

namespace Modules\Payments\Tests\Unit;

use Modules\Payments\Models\Payment;
use Modules\Payments\Services\PaymentService;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\PaymentMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\AbstractServiceTestCase;

#[CoversClass(PaymentService::class)]
class PaymentServiceTest extends AbstractServiceTestCase
{
    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentService();
    }

    #[Group('crud')]
    #[Test]
    public function it_returns_validation_rules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('payment_method_id', $rules);
        $this->assertArrayHasKey('payment_amount', $rules);
        $this->assertArrayHasKey('payment_date', $rules);
    }

    #[Group('relationships')]
    #[Test]
    public function it_finds_payment_with_relations(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
        ]);

        /** Act */
        $result = $this->service->findWithRelations($payment->payment_id);

        /** Assert */
        $this->assertNotNull($result);
        $this->assertEquals($payment->payment_id, $result->payment_id);
        $this->assertTrue($result->relationLoaded('invoice'));
        $this->assertTrue($result->relationLoaded('paymentMethod'));
        $this->assertNotNull($result->invoice);
        $this->assertNotNull($result->paymentMethod);
    }

    #[Group('relationships')]
    #[Test]
    public function it_finds_payment_with_custom_relations(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
        ]);

        /** Act */
        $result = $this->service->findWithRelations($payment->payment_id, ['invoice']);

        /** Assert */
        $this->assertNotNull($result);
        $this->assertTrue($result->relationLoaded('invoice'));
        $this->assertFalse($result->relationLoaded('paymentMethod'));
    }

    #[Group('relationships')]
    #[Test]
    public function it_returns_null_when_payment_not_found(): void
    {
        /** Act */
        $result = $this->service->findWithRelations(99999);

        /** Assert */
        $this->assertNull($result);
    }

    #[Group('relationships')]
    #[Test]
    public function it_gets_all_payments_with_relations_paginated(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $paymentMethod = PaymentMethod::factory()->create();
        
        Payment::factory()->count(3)->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_method_id' => $paymentMethod->payment_method_id,
            'payment_date' => now()->subDays(1),
        ]);

        /** Act */
        $result = $this->service->getAllWithRelations();

        /** Assert */
        $this->assertGreaterThanOrEqual(3, $result->total());
        $this->assertTrue($result->first()->relationLoaded('invoice'));
        $this->assertTrue($result->first()->relationLoaded('paymentMethod'));
    }

    #[Group('relationships')]
    #[Test]
    public function it_orders_payments_by_date_descending(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $payment1 = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(3),
        ]);
        $payment2 = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(1),
        ]);
        $payment3 = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(2),
        ]);

        /** Act */
        $result = $this->service->getAllWithRelations();

        /** Assert */
        $payments = $result->items();
        $this->assertGreaterThanOrEqual(3, count($payments));
        // Most recent should be first
        $this->assertEquals($payment2->payment_id, $payments[0]->payment_id);
    }

    #[Group('relationships')]
    #[Test]
    public function it_respects_custom_per_page_parameter(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        Payment::factory()->count(10)->create([
            'invoice_id' => $invoice->invoice_id,
        ]);

        /** Act */
        $result = $this->service->getAllWithRelations(['invoice'], 5);

        /** Assert */
        $this->assertEquals(5, $result->perPage());
    }
}
