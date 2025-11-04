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
    public function it_orders_payments_by_date_descending(): void
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(3),
        ]);
        $payment2 = Payment::factory()->create([
            'invoice_id' => $invoice->invoice_id,
            'payment_date' => now()->subDays(1),
        ]);
        Payment::factory()->create([
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
}
