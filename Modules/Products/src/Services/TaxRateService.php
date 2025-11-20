<?php

namespace Modules\Products\Services;

use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\InvoiceTaxRate;
use Modules\Invoices\Models\Item as InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Products\Models\TaxRate;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteTaxRate;

/**
 * TaxRateService.
 *
 * Service class for managing tax rate business logic
 */
class TaxRateService extends BaseService
{
    /**
     * Get all tax rates.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return TaxRate::query()->get();
    }

    /**
     * Get all tax rates ordered by name.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllOrdered()
    {
        return TaxRate::query()->orderBy('tax_rate_name')->get();
    }

    /**
     * Check if a tax rate can be deleted.
     *
     * A tax rate cannot be deleted if it is used by:
     * - Products
     * - Invoice items
     * - Invoice tax rates
     * - Quote items
     * - Quote tax rates
     *
     * @param int $taxRateId
     *
     * @return bool
     */
    public function canDelete(int $taxRateId): bool
    {
        // Check products
        if (Product::query()->where('tax_rate_id', $taxRateId)->exists()) {
            return false;
        }

        // Check invoice items
        if (InvoiceItem::query()->where('item_tax_rate_id', $taxRateId)->exists()) {
            return false;
        }

        // Check invoice tax rates
        if (InvoiceTaxRate::query()->where('tax_rate_id', $taxRateId)->exists()) {
            return false;
        }

        // Check quote items
        if (QuoteItem::query()->where('item_tax_rate_id', $taxRateId)->exists()) {
            return false;
        }

        // Check quote tax rates
        return ! (QuoteTaxRate::query()->where('tax_rate_id', $taxRateId)->exists());
    }

    /**
     * Get deletion blocker details for a tax rate.
     *
     * @param int $taxRateId
     *
     * @return array
     */
    public function getDeletionBlockers(int $taxRateId): array
    {
        return [
            'products'          => Product::query()->where('tax_rate_id', $taxRateId)->count(),
            'invoice_items'     => InvoiceItem::query()->where('item_tax_rate_id', $taxRateId)->count(),
            'invoice_tax_rates' => InvoiceTaxRate::query()->where('tax_rate_id', $taxRateId)->count(),
            'quote_items'       => QuoteItem::query()->where('item_tax_rate_id', $taxRateId)->count(),
            'quote_tax_rates'   => QuoteTaxRate::query()->where('tax_rate_id', $taxRateId)->count(),
        ];
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return TaxRate::class;
    }
}
