<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Quotes\Services\QuoteAmountService;
use Modules\Quotes\Services\QuoteItemAmountService;
use Modules\Quotes\Services\QuoteItemService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\AbstractServiceTestCase;

#[CoversClass(QuoteItemService::class)]
class QuoteItemServiceTest extends AbstractServiceTestCase
{
    private QuoteItemService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $quoteService = $this->createMock(\Modules\Quotes\Services\QuoteService::class);
        $quoteAmountService = new QuoteAmountService($quoteService);
        $quoteItemAmountService = new QuoteItemAmountService();
        $this->service = new QuoteItemService($quoteAmountService, $quoteItemAmountService);
    }

    #[Group('crud')]
    #[Test]
    public function it_returns_validation_rules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('quote_id', $rules);
        $this->assertArrayHasKey('item_name', $rules);
        $this->assertArrayHasKey('item_description', $rules);
        $this->assertArrayHasKey('item_quantity', $rules);
        $this->assertArrayHasKey('item_price', $rules);
    }

    #[Group('crud')]
    #[Test]
    public function it_saves_item(): void
    {
        $this->markTestIncomplete('Requires database setup');
    }

    #[Group('crud')]
    #[Test]
    public function it_deletes_item(): void
    {
        $this->markTestIncomplete('Requires database setup');
    }

    #[Test]
    public function it_gets_items_subtotal(): void
    {
        $this->markTestIncomplete('Requires database setup');
    }
}
