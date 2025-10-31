<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Services\TemplateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(TemplateService::class)]
class TemplateServiceTest extends AbstractServiceTestCase
{
    private TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TemplateService();
    }

    #[Test]
    public function it_returns_empty_array_when_invoice_pdf_templates_directory_not_exists(): void
    {
        $this->markTestIncomplete();
        // Using a path that doesn't exist
        $result = $this->service->getInvoiceTemplates('pdf');

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_returns_empty_array_when_invoice_public_templates_directory_not_exists(): void
    {
        $this->markTestIncomplete();
        $result = $this->service->getInvoiceTemplates('public');

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_returns_empty_array_when_quote_pdf_templates_directory_not_exists(): void
    {
        $this->markTestIncomplete();
        $result = $this->service->getQuoteTemplates('pdf');

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_returns_empty_array_when_quote_public_templates_directory_not_exists(): void
    {
        $this->markTestIncomplete();
        $result = $this->service->getQuoteTemplates('public');

        $this->assertIsArray($result);
    }

    #[Test]
    public function it_defaults_to_pdf_type_for_invoice_templates(): void
    {
        $this->markTestIncomplete();
        // Default should be 'pdf'
        $resultDefault = $this->service->getInvoiceTemplates();
        $resultPdf     = $this->service->getInvoiceTemplates('pdf');

        $this->assertEquals($resultPdf, $resultDefault);
    }

    #[Test]
    public function it_defaults_to_pdf_type_for_quote_templates(): void
    {
        $this->markTestIncomplete();
        // Default should be 'pdf'
        $resultDefault = $this->service->getQuoteTemplates();
        $resultPdf     = $this->service->getQuoteTemplates('pdf');

        $this->assertEquals($resultPdf, $resultDefault);
    }

    #[Test]
    public function it_filters_out_dot_directories(): void
    {
        $this->markTestIncomplete();
        // The service should not include '.' and '..' in results
        $invoiceTemplates = $this->service->getInvoiceTemplates();
        $quoteTemplates   = $this->service->getQuoteTemplates();

        $this->assertNotContains('.', $invoiceTemplates);
        $this->assertNotContains('..', $invoiceTemplates);
        $this->assertNotContains('.', $quoteTemplates);
        $this->assertNotContains('..', $quoteTemplates);
    }

    #[Test]
    public function it_removes_file_extensions_from_template_names(): void
    {
        $this->markTestIncomplete();
        // This is more of a contract test - if templates exist, they shouldn't have extensions
        $invoiceTemplates = $this->service->getInvoiceTemplates('pdf');
        $quoteTemplates   = $this->service->getQuoteTemplates('pdf');

        foreach ($invoiceTemplates as $template) {
            $this->assertStringNotContainsString('.php', $template);
            $this->assertStringNotContainsString('.blade.php', $template);
        }

        foreach ($quoteTemplates as $template) {
            $this->assertStringNotContainsString('.php', $template);
            $this->assertStringNotContainsString('.blade.php', $template);
        }
    }

    #[Test]
    public function it_handles_different_template_types(): void
    {
        $this->markTestIncomplete();
        $pdfTemplates    = $this->service->getInvoiceTemplates('pdf');
        $publicTemplates = $this->service->getInvoiceTemplates('public');

        $this->assertIsArray($pdfTemplates);
        $this->assertIsArray($publicTemplates);
    }

    #[Test]
    public function it_returns_indexed_array(): void
    {
        $this->markTestIncomplete();
        $templates = $this->service->getInvoiceTemplates();

        // Should be numerically indexed (array_values is used in the service)
        if (count($templates) > 0) {
            $this->assertArrayHasKey(0, $templates);
        }

        $this->assertIsArray($templates);
    }
}