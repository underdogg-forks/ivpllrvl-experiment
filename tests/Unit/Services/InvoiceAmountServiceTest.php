<?php

namespace Tests\Unit\Services;

use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Setting;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;
use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\ItemAmount;
use Modules\Invoices\Services\InvoiceAmountService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceAmountService::class)]
class InvoiceAmountServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('ip_invoice_amounts')->delete();
        DB::table('ip_invoice_item_amounts')->delete();
        DB::table('ip_invoice_items')->delete();
        DB::table('ip_payments')->delete();
        DB::table('ip_invoices')->delete();

        Setting::setValue('tax_rate_decimal_places', '2');
        Setting::setValue('legacy_calculation', '0');
    }

    #[Test]
    public function it_calculates_invoice_totals_with_payments(): void
    {
        $invoice = Invoice::query()->create([
            'client_id'               => 1,
            'user_id'                 => 1,
            'invoice_group_id'        => 1,
            'invoice_status_id'       => 1,
            'invoice_number'          => 'INV-1000',
            'invoice_date_created'    => '2024-01-01',
            'invoice_date_modified'   => '2024-01-01',
            'invoice_date_due'        => '2024-01-15',
            'invoice_password'        => '',
            'invoice_discount_amount' => 0,
            'invoice_discount_percent'=> 0,
            'invoice_terms'           => '',
            'invoice_url_key'         => 'key-1000',
        ]);

        $firstItem = Item::query()->create([
            'invoice_id'           => $invoice->invoice_id,
            'item_tax_rate_id'     => null,
            'item_product_id'      => null,
            'item_name'            => 'Consulting',
            'item_description'     => 'Consulting hours',
            'item_quantity'        => 2,
            'item_price'           => 100,
            'item_order'           => 1,
            'item_discount_amount' => 0,
            'item_product_unit'    => null,
            'item_product_unit_id' => null,
        ]);

        $secondItem = Item::query()->create([
            'invoice_id'           => $invoice->invoice_id,
            'item_tax_rate_id'     => null,
            'item_product_id'      => null,
            'item_name'            => 'Support',
            'item_description'     => 'Support plan',
            'item_quantity'        => 1,
            'item_price'           => 150,
            'item_order'           => 2,
            'item_discount_amount' => 0,
            'item_product_unit'    => null,
            'item_product_unit_id' => null,
        ]);

        ItemAmount::query()->create([
            'item_id'        => $firstItem->item_id,
            'item_subtotal'  => 200,
            'item_tax_total' => 20,
            'item_discount'  => 0,
            'item_total'     => 220,
        ]);

        ItemAmount::query()->create([
            'item_id'        => $secondItem->item_id,
            'item_subtotal'  => 150,
            'item_tax_total' => 15,
            'item_discount'  => 0,
            'item_total'     => 165,
        ]);

        DB::table('ip_payments')->insert([
            'invoice_id'     => $invoice->invoice_id,
            'payment_amount' => 100,
            'payment_method' => 1,
            'payment_date'   => '2024-01-10',
        ]);

        $service = app(InvoiceAmountService::class);
        $service->calculate($invoice->invoice_id);

        $amount = InvoiceAmount::query()->where('invoice_id', $invoice->invoice_id)->firstOrFail();

        $this->assertEquals(350.0, (float) $amount->invoice_item_subtotal);
        $this->assertEquals(35.0, (float) $amount->invoice_item_tax_total);
        $this->assertEquals(385.0, (float) $amount->invoice_total);
        $this->assertEquals(100.0, (float) $amount->invoice_paid);
        $this->assertEquals(285.0, (float) $amount->invoice_balance);
        $this->assertEquals(0.0, (float) $amount->invoice_tax_total);
    }
}
