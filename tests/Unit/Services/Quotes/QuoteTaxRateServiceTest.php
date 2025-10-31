<?php

namespace Tests\Unit\Services\Quotes;

use Modules\Quotes\Services\QuoteAmountService;
use Modules\Quotes\Services\QuoteTaxRateService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\Services\AbstractServiceTestCase;

#[CoversClass(QuoteTaxRateService::class)]
class QuoteTaxRateServiceTest extends AbstractServiceTestCase
{
    private QuoteTaxRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $quoteService = $this->createMock(\Modules\Quotes\Services\QuoteService::class);
        $quoteAmountService = new QuoteAmountService($quoteService);
        $this->service = new QuoteTaxRateService($quoteAmountService);
    }

    #[Test]
    public function it_returns_validation_rules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('quote_id', $rules);
        $this->assertArrayHasKey('tax_rate_id', $rules);
        $this->assertArrayHasKey('include_item_tax', $rules);
    }

    #[Test]
    public function it_saves_tax_rate_in_legacy_mode(): void
    {
        $this->markTestIncomplete('Requires database setup and legacy mode');
    }

    #[Test]
    public function it_returns_null_when_not_in_legacy_mode(): void
    {
        $this->markTestIncomplete('Requires config_item mock');
    }
}
