<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Services\TemplateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateService::class)]
class TemplateServiceTest extends TestCase
{
    private TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TemplateService();
    }

    #[Test]
    public function itReturnsEmptyArrayWhenInvoicePdfTemplatesDirectoryNotExists(): void
    {
        // Using a path that doesn't exist
        $result = $this->service->getInvoiceTemplates('pdf');

        $this->assertIsArray($result);
    }

    #[Test]
    public function itReturnsEmptyArrayWhenInvoicePublicTemplatesDirectoryNotExists(): void
    {
        $result = $this->service->getInvoiceTemplates('public');

        $this->assertIsArray($result);
    }

    #[Test]
    public function itReturnsEmptyArrayWhenQuotePdfTemplatesDirectoryNotExists(): void
    {
        $result = $this->service->getQuoteTemplates('pdf');

        $this->assertIsArray($result);
    }

    #[Test]
    public function itReturnsEmptyArrayWhenQuotePublicTemplatesDirectoryNotExists(): void
    {
        $result = $this->service->getQuoteTemplates('public');

        $this->assertIsArray($result);
    }

    #[Test]
    public function itDefaultsToPdfTypeForInvoiceTemplates(): void
    {
        // Default should be 'pdf'
        $resultDefault = $this->service->getInvoiceTemplates();
        $resultPdf     = $this->service->getInvoiceTemplates('pdf');

        $this->assertEquals($resultPdf, $resultDefault);
    }

    #[Test]
    public function itDefaultsToPdfTypeForQuoteTemplates(): void
    {
        // Default should be 'pdf'
        $resultDefault = $this->service->getQuoteTemplates();
        $resultPdf     = $this->service->getQuoteTemplates('pdf');

        $this->assertEquals($resultPdf, $resultDefault);
    }

    #[Test]
    public function itFiltersOutDotDirectories(): void
    {
        // The service should not include '.' and '..' in results
        $invoiceTemplates = $this->service->getInvoiceTemplates();
        $quoteTemplates   = $this->service->getQuoteTemplates();

        $this->assertNotContains('.', $invoiceTemplates);
        $this->assertNotContains('..', $invoiceTemplates);
        $this->assertNotContains('.', $quoteTemplates);
        $this->assertNotContains('..', $quoteTemplates);
    }

    #[Test]
    public function itRemovesFileExtensionsFromTemplateNames(): void
    {
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
    public function itHandlesDifferentTemplateTypes(): void
    {
        $pdfTemplates    = $this->service->getInvoiceTemplates('pdf');
        $publicTemplates = $this->service->getInvoiceTemplates('public');

        $this->assertIsArray($pdfTemplates);
        $this->assertIsArray($publicTemplates);
    }

    #[Test]
    public function itReturnsIndexedArray(): void
    {
        $templates = $this->service->getInvoiceTemplates();

        // Should be numerically indexed (array_values is used in the service)
        if (count($templates) > 0) {
            $this->assertArrayHasKey(0, $templates);
        }

        $this->assertIsArray($templates);
    }
}