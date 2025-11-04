<?php

namespace Modules\Invoices\Tests\Unit;

use Modules\Invoices\Services\InvoicesRecurringService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InvoicesRecurringService::class)]
class InvoicesRecurringServiceTest extends AbstractServiceTestCase
{
    private InvoicesRecurringService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoicesRecurringService();
    }

    #[Group('crud')]
    #[Test]
    public function it_returns_validation_rules(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('recur_start_date', $rules);
        $this->assertArrayHasKey('recur_end_date', $rules);
        $this->assertArrayHasKey('recur_frequency', $rules);
        $this->assertArrayHasKey('recur_next_date', $rules);
    }

    #[Test]
    public function it_validates_invoice_id_as_required_integer(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['invoice_id']);
        $this->assertStringContainsString('integer', $rules['invoice_id']);
    }

    #[Test]
    public function it_validates_recur_start_date_as_required_date(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['recur_start_date']);
        $this->assertStringContainsString('date', $rules['recur_start_date']);
    }

    #[Test]
    public function it_validates_recur_end_date_as_nullable_date(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['recur_end_date']);
        $this->assertStringContainsString('date', $rules['recur_end_date']);
    }

    #[Test]
    public function it_validates_recur_frequency_as_required_string(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['recur_frequency']);
        $this->assertStringContainsString('string', $rules['recur_frequency']);
    }

    #[Test]
    public function it_validates_recur_next_date_as_nullable_date(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['recur_next_date']);
        $this->assertStringContainsString('date', $rules['recur_next_date']);
    }

    #[Test]
    public function it_provides_all_required_validation_keys(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $expectedKeys = [
            'invoice_id',
            'recur_start_date',
            'recur_end_date',
            'recur_frequency',
            'recur_next_date',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $rules);
        }
    }

    #[Group('relationships')]
    #[Test]
    public function it_gets_all_recurring_invoices_with_relations_paginated(): void
    {
        /** Arrange */
        $client  = \Modules\Crm\Models\Client::factory()->create();
        $invoice = \Modules\Invoices\Models\Invoice::factory()->create([
            'client_id' => $client->client_id,
        ]);

        \Modules\Invoices\Models\InvoicesRecurring::factory()->count(3)->create([
            'invoice_id' => $invoice->invoice_id,
            'client_id'  => $client->client_id,
        ]);

        /** Act */
        $result = $this->service->getAllWithRelations();

        /* Assert */
        $this->assertGreaterThanOrEqual(3, $result->total());
        $this->assertTrue($result->first()->relationLoaded('invoice'));
        $this->assertTrue($result->first()->relationLoaded('client'));
    }

    #[Group('relationships')]
    #[Test]
    public function it_respects_custom_per_page_parameter(): void
    {
        /** Arrange */
        $client  = \Modules\Crm\Models\Client::factory()->create();
        $invoice = \Modules\Invoices\Models\Invoice::factory()->create([
            'client_id' => $client->client_id,
        ]);

        \Modules\Invoices\Models\InvoicesRecurring::factory()->count(10)->create([
            'invoice_id' => $invoice->invoice_id,
            'client_id'  => $client->client_id,
        ]);

        /** Act */
        $result = $this->service->getAllWithRelations(['invoice'], 5);

        /* Assert */
        $this->assertEquals(5, $result->perPage());
    }
}
