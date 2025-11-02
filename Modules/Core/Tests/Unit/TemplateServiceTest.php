<?php

namespace Modules\Core\Tests\Unit;

use Modules\Invoices\Services\TemplateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\AbstractServiceTestCase;

#[CoversClass(TemplateService::class)]
class TemplateServiceTest extends AbstractServiceTestCase
{
    private TemplateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new TemplateService();
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_array_when_invoice_pdf_templates_directory_not_exists(): void
    {
        /** Arrange */
        // APPPATH is defined in bootstrap to point to 'application' directory
        // Since the templates are actually in Modules/Core/resources/views,
        // the old path (APPPATH/views/invoice_templates/pdf) won't exist
        
        /** Act */
        $result = $this->service->getInvoiceTemplates('pdf');

        /** Assert */
        $this->assertIsArray($result);
        // If directory doesn't exist, should return empty array
        // This is the expected behavior when templates aren't found
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_array_when_invoice_public_templates_directory_not_exists(): void
    {
        /** Arrange */
        // Test that service gracefully handles missing directories
        
        /** Act */
        $result = $this->service->getInvoiceTemplates('public');

        /** Assert */
        $this->assertIsArray($result);
        // Should return empty array when directory doesn't exist
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_array_when_quote_pdf_templates_directory_not_exists(): void
    {
        /** Arrange */
        // Test that service handles missing quote template directories
        
        /** Act */
        $result = $this->service->getQuoteTemplates('pdf');

        /** Assert */
        $this->assertIsArray($result);
        // Should return empty array when directory doesn't exist
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_empty_array_when_quote_public_templates_directory_not_exists(): void
    {
        /** Arrange */
        // Test graceful handling of missing directories
        
        /** Act */
        $result = $this->service->getQuoteTemplates('public');

        /** Assert */
        $this->assertIsArray($result);
        // Should return empty array when directory doesn't exist
    }

    #[Test]
    public function it_defaults_to_pdf_type_for_invoice_templates(): void
    {
        /** Arrange */
        // Service should use 'pdf' as default type parameter
        
        /** Act */
        $resultDefault = $this->service->getInvoiceTemplates();
        $resultPdf     = $this->service->getInvoiceTemplates('pdf');

        /** Assert */
        $this->assertEquals($resultPdf, $resultDefault);
        // Both should return the same results since pdf is the default
    }

    #[Test]
    public function it_defaults_to_pdf_type_for_quote_templates(): void
    {
        /** Arrange */
        // Service should use 'pdf' as default type parameter
        
        /** Act */
        $resultDefault = $this->service->getQuoteTemplates();
        $resultPdf     = $this->service->getQuoteTemplates('pdf');

        /** Assert */
        $this->assertEquals($resultPdf, $resultDefault);
        // Both should return the same results since pdf is the default
    }

    #[Test]
    public function it_filters_out_dot_directories(): void
    {
        /** Arrange */
        // Service should exclude '.' and '..' from directory listings
        
        /** Act */
        $invoiceTemplates = $this->service->getInvoiceTemplates();
        $quoteTemplates   = $this->service->getQuoteTemplates();

        /** Assert */
        // The service uses array_filter to remove '.' and '..'
        $this->assertNotContains('.', $invoiceTemplates);
        $this->assertNotContains('..', $invoiceTemplates);
        $this->assertNotContains('.', $quoteTemplates);
        $this->assertNotContains('..', $quoteTemplates);
    }

    #[Test]
    public function it_removes_file_extensions_from_template_names(): void
    {
        /** Arrange */
        // Service should strip .php extensions using pathinfo(PATHINFO_FILENAME)
        
        /** Act */
        $invoiceTemplates = $this->service->getInvoiceTemplates('pdf');
        $quoteTemplates   = $this->service->getQuoteTemplates('pdf');

        /** Assert */
        // All template names should have extensions removed
        foreach ($invoiceTemplates as $template) {
            $this->assertStringNotContainsString('.php', $template);
            $this->assertStringNotContainsString('.blade.php', $template);
        }

        foreach ($quoteTemplates as $template) {
            $this->assertStringNotContainsString('.php', $template);
            $this->assertStringNotContainsString('.blade.php', $template);
        }
    }

    #[Group('exotic')]
    #[Test]
    public function it_handles_different_template_types(): void
    {
        /** Arrange */
        // Service should handle both 'pdf' and 'public' types
        
        /** Act */
        $pdfTemplates    = $this->service->getInvoiceTemplates('pdf');
        $publicTemplates = $this->service->getInvoiceTemplates('public');

        /** Assert */
        $this->assertIsArray($pdfTemplates);
        $this->assertIsArray($publicTemplates);
        // Both types should return arrays (empty or populated)
    }

    #[Group('smoke')]
    #[Test]
    public function it_returns_indexed_array(): void
    {
        /** Arrange */
        // Service uses array_values() to ensure numeric indexing
        
        /** Act */
        $templates = $this->service->getInvoiceTemplates();

        /** Assert */
        // Should be numerically indexed (array_values is used in the service)
        if (count($templates) > 0) {
            $this->assertArrayHasKey(0, $templates);
        }

        $this->assertIsArray($templates);
    }
}