<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Services\InvoiceSumexService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InvoiceSumexService::class)]
class InvoiceSumexServiceTest extends AbstractServiceTestCase
{
    private InvoiceSumexService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoiceSumexService();
    }

    #[Test]
    public function it_returns_validation_rules(): void
    {
        $this->markTestIncomplete();
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
    public function it_validates_sumex_invoice_as_required_integer(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('required', $rules['sumex_invoice']);
        $this->assertStringContainsString('integer', $rules['sumex_invoice']);
    }

    #[Test]
    public function it_validates_optional_fields_as_nullable(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('nullable', $rules['sumex_reason']);
        $this->assertStringContainsString('nullable', $rules['sumex_diagnosis']);
        $this->assertStringContainsString('nullable', $rules['sumex_observations']);
        $this->assertStringContainsString('nullable', $rules['sumex_casenumber']);
    }

    #[Test]
    public function it_validates_date_fields(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('date', $rules['sumex_treatmentstart']);
        $this->assertStringContainsString('date', $rules['sumex_treatmentend']);
        $this->assertStringContainsString('date', $rules['sumex_casedate']);
    }

    #[Test]
    public function it_validates_string_fields(): void
    {
        $this->markTestIncomplete();
        $rules = $this->service->getValidationRules();

        $this->assertStringContainsString('string', $rules['sumex_diagnosis']);
        $this->assertStringContainsString('string', $rules['sumex_observations']);
        $this->assertStringContainsString('string', $rules['sumex_casenumber']);
    }
}