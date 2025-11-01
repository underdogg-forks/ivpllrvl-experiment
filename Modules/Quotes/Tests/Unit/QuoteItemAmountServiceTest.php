<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Quotes\Services\QuoteItemAmountService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Tests\AbstractServiceTestCase;

#[CoversClass(QuoteItemAmountService::class)]
class QuoteItemAmountServiceTest extends AbstractServiceTestCase
{
    private QuoteItemAmountService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuoteItemAmountService();
    }

    #[Group('exotic')]
    #[Test]
    public function it_calculates_item_amounts_in_legacy_mode(): void
    {
        $this->markTestIncomplete('Requires database setup with quote items');
    }

    #[Group('exotic')]
    #[Test]
    public function it_calculates_item_amounts_in_new_mode(): void
    {
        $this->markTestIncomplete('Requires database setup with quote items');
    }

    #[Test]
    public function it_applies_global_discount_proportionally(): void
    {
        $this->markTestIncomplete('Requires database setup');
    }
}
