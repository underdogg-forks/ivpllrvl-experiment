<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Services\InvoicesRecurringService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoicesRecurringService::class)]
class InvoicesRecurringServiceTest extends TestCase
{
    private InvoicesRecurringService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoicesRecurringService();
    }

    #[Test]
    public function itReturnsValidationRules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('invoice_id', $rules);
        $this->assertArrayHasKey('recur_start_date', $rules);
        $this->assertArrayHasKey('recur_end_date', $rules);
        $this->assertArrayHasKey('recur_frequency', $rules);
        $this->assertArrayHasKey('recur_next_date', $rules);
    }

    #[Test]
    public function itValidatesInvoiceIdAsRequiredInteger(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['invoice_id']);
        $this->assertStringContainsString('integer', $rules['invoice_id']);
    }

    #[Test]
    public function itValidatesRecurStartDateAsRequiredDate(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['recur_start_date']);
        $this->assertStringContainsString('date', $rules['recur_start_date']);
    }

    #[Test]
    public function itValidatesRecurEndDateAsNullableDate(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['recur_end_date']);
        $this->assertStringContainsString('date', $rules['recur_end_date']);
    }

    #[Test]
    public function itValidatesRecurFrequencyAsRequiredString(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['recur_frequency']);
        $this->assertStringContainsString('string', $rules['recur_frequency']);
    }

    #[Test]
    public function itValidatesRecurNextDateAsNullableDate(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['recur_next_date']);
        $this->assertStringContainsString('date', $rules['recur_next_date']);
    }

    #[Test]
    public function itProvidesAllRequiredValidationKeys(): void
    {
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
}