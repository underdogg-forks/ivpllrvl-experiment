<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Services\InvoiceSumexService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceSumexService::class)]
class InvoiceSumexServiceTest extends TestCase
{
    private InvoiceSumexService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoiceSumexService();
    }

    #[Test]
    public function itReturnsValidationRules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('sumex_invoice', $rules);
        $this->assertArrayHasKey('sumex_reason', $rules);
        $this->assertArrayHasKey('sumex_diagnosis', $rules);
        $this->assertArrayHasKey('sumex_observations', $rules);
        $this->assertArrayHasKey('sumex_treatmentstart', $rules);
        $this->assertArrayHasKey('sumex_treatmentend', $rules);
        $this->assertArrayHasKey('sumex_casedate', $rules);
        $this->assertArrayHasKey('sumex_casenumber', $rules);
    }

    #[Test]
    public function itValidatesSumexInvoiceAsRequiredInteger(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['sumex_invoice']);
        $this->assertStringContainsString('integer', $rules['sumex_invoice']);
    }

    #[Test]
    public function itValidatesOptionalFieldsAsNullable(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['sumex_reason']);
        $this->assertStringContainsString('nullable', $rules['sumex_diagnosis']);
        $this->assertStringContainsString('nullable', $rules['sumex_observations']);
        $this->assertStringContainsString('nullable', $rules['sumex_casenumber']);
    }

    #[Test]
    public function itValidatesDateFields(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('date', $rules['sumex_treatmentstart']);
        $this->assertStringContainsString('date', $rules['sumex_treatmentend']);
        $this->assertStringContainsString('date', $rules['sumex_casedate']);
    }

    #[Test]
    public function itValidatesStringFields(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('string', $rules['sumex_diagnosis']);
        $this->assertStringContainsString('string', $rules['sumex_observations']);
        $this->assertStringContainsString('string', $rules['sumex_casenumber']);
    }
}