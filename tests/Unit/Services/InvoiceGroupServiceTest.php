<?php

namespace Tests\Unit\Services;

use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Services\InvoiceGroupService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\Unit\UnitTestCase;

#[CoversClass(InvoiceGroupService::class)]
class InvoiceGroupServiceTest extends UnitTestCase
{
    private InvoiceGroupService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new InvoiceGroupService();

        $this->cleanupTables(['ip_invoice_groups']);
    }

    #[Test]
    public function itReturnsValidationRules(): void
    {
        $rules = $this->service->getValidationRules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('invoice_group_name', $rules);
        $this->assertArrayHasKey('invoice_group_identifier_format', $rules);
        $this->assertArrayHasKey('invoice_group_next_id', $rules);
        $this->assertArrayHasKey('invoice_group_left_pad', $rules);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithYearTemplate(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'INV-{{{year}}}-{{{id}}}',
            'invoice_group_next_id'           => 1,
            'invoice_group_left_pad'          => 4,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $expectedYear = date('Y');
        $this->assertEquals("INV-{$expectedYear}-0001", $number);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithMonthTemplate(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{month}}}/{{{id}}}',
            'invoice_group_next_id'           => 5,
            'invoice_group_left_pad'          => 3,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $expectedMonth = date('m');
        $this->assertEquals("{$expectedMonth}/005", $number);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithDayTemplate(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{day}}}-{{{id}}}',
            'invoice_group_next_id'           => 10,
            'invoice_group_left_pad'          => 2,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $expectedDay = date('d');
        $this->assertEquals("{$expectedDay}-10", $number);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithShortYearTemplate(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{yy}}}{{{id}}}',
            'invoice_group_next_id'           => 100,
            'invoice_group_left_pad'          => 5,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $expectedYY = date('y');
        $this->assertEquals("{$expectedYY}00100", $number);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithMultipleTemplates(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{year}}}/{{{month}}}/{{{id}}}',
            'invoice_group_next_id'           => 1,
            'invoice_group_left_pad'          => 6,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $expectedYear  = date('Y');
        $expectedMonth = date('m');
        $this->assertEquals("{$expectedYear}/{$expectedMonth}/000001", $number);
    }

    #[Test]
    public function itGeneratesInvoiceNumberWithoutTemplates(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'STATIC-PREFIX',
            'invoice_group_next_id'           => 999,
            'invoice_group_left_pad'          => 0,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $this->assertEquals('STATIC-PREFIX', $number);
    }

    #[Test]
    public function itIncrementsNextIdWhenSetNextIsTrue(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'INV-{{{id}}}',
            'invoice_group_next_id'           => 50,
            'invoice_group_left_pad'          => 3,
        ]);

        $this->service->generateInvoiceNumber($group, true);

        $group->refresh();
        $this->assertEquals(51, $group->invoice_group_next_id);
    }

    #[Test]
    public function itDoesNotIncrementNextIdWhenSetNextIsFalse(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'INV-{{{id}}}',
            'invoice_group_next_id'           => 50,
            'invoice_group_left_pad'          => 3,
        ]);

        $this->service->generateInvoiceNumber($group, false);

        $group->refresh();
        $this->assertEquals(50, $group->invoice_group_next_id);
    }

    #[Test]
    public function itPadsInvoiceIdWithZeros(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{id}}}',
            'invoice_group_next_id'           => 7,
            'invoice_group_left_pad'          => 10,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $this->assertEquals('0000000007', $number);
    }

    #[Test]
    public function itHandlesZeroLeftPad(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => 'INV{{{id}}}',
            'invoice_group_next_id'           => 123,
            'invoice_group_left_pad'          => 0,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $this->assertEquals('INV123', $number);
    }

    #[Test]
    public function itHandlesUnknownTemplateVariables(): void
    {
        $group = InvoiceGroup::query()->create([
            'invoice_group_name'              => 'Test Group',
            'invoice_group_identifier_format' => '{{{unknown}}}-{{{id}}}',
            'invoice_group_next_id'           => 1,
            'invoice_group_left_pad'          => 2,
        ]);

        $number = $this->service->generateInvoiceNumber($group, false);

        $this->assertEquals('-01', $number); // Unknown variable replaced with empty string
    }
}