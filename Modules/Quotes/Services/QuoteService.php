<?php

namespace Modules\Quotes\Services;

use DateInterval;
use DateTime;
use Modules\Core\Models\QuoteCustom;
use Modules\Core\Support\SettingsHelper;
use Modules\Core\Support\TranslationHelper;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Invoices\Services\InvoiceGroupService;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteAmount;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteTaxRate;

/**
 * QuoteService.
 *
 * Service class for managing quote business logic
 * Extracted from Quote model
 */
class QuoteService
{
    /**
     * Get quote statuses.
     *
     * @return array
     */
    public function getStatuses(): array
    {
        return [
            '1' => [
                'label' => TranslationHelper::trans('draft'),
                'class' => 'draft',
                'href'  => 'quotes/status/draft',
            ],
            '2' => [
                'label' => TranslationHelper::trans('sent'),
                'class' => 'sent',
                'href'  => 'quotes/status/sent',
            ],
            '3' => [
                'label' => TranslationHelper::trans('viewed'),
                'class' => 'viewed',
                'href'  => 'quotes/status/viewed',
            ],
            '4' => [
                'label' => TranslationHelper::trans('approved'),
                'class' => 'approved',
                'href'  => 'quotes/status/approved',
            ],
            '5' => [
                'label' => TranslationHelper::trans('rejected'),
                'class' => 'rejected',
                'href'  => 'quotes/status/rejected',
            ],
            '6' => [
                'label' => TranslationHelper::trans('canceled'),
                'class' => 'canceled',
                'href'  => 'quotes/status/canceled',
            ],
        ];
    }

    /**
     * Get validation rules for creating a quote.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'client_id'          => 'required|integer',
            'quote_date_created' => 'required|date',
            'invoice_group_id'   => 'required|integer',
            'quote_password'     => 'nullable|string',
            'user_id'            => 'required|integer',
        ];
    }

    /**
     * Get validation rules for saving a quote.
     *
     * @param int|null $quoteId
     *
     * @return array
     */
    public function getSaveValidationRules(?int $quoteId = null): array
    {
        $uniqueRule = 'unique:ip_quotes,quote_number';
        if ($quoteId) {
            $uniqueRule .= ',' . $quoteId . ',quote_id';
        }

        return [
            'quote_number'       => $uniqueRule,
            'quote_date_created' => 'required|date',
            'quote_date_expires' => 'required|date',
            'quote_password'     => 'nullable|string',
        ];
    }

    /**
     * Create a new quote with associated records.
     *
     * @param array $data
     *
     * @return Quote
     */
    public function createQuote(array $data): Quote
    {
        // Create the quote
        $quote = Quote::create($data);

        // Create quote amount record
        QuoteAmount::create([
            'quote_id' => $quote->quote_id,
        ]);

        // Create default quote tax rate if applicable
        $defaultTaxRate = SettingsHelper::getSetting('default_invoice_tax_rate');
        if ($defaultTaxRate) {
            QuoteTaxRate::create([
                'quote_id'              => $quote->quote_id,
                'tax_rate_id'           => $defaultTaxRate,
                'include_item_tax'      => SettingsHelper::getSetting('default_include_item_tax'),
                'quote_tax_rate_amount' => 0,
            ]);
        }

        return $quote;
    }

    /**
     * Copy quote items, tax rates, and custom fields from source to target.
     *
     * @param int $sourceId
     * @param int $targetId
     *
     * @return void
     */
    public function copyQuote(int $sourceId, int $targetId): void
    {
        $sourceQuote = Quote::query()->with(['items', 'taxRates'])->findOrFail($sourceId);

        // Calculate global discount
        $itemsSubtotal  = QuoteItem::query()->where('quote_id', $sourceId)->sum('item_subtotal');
        $globalDiscount = [
            'amount'         => $sourceQuote->quote_discount_amount,
            'percent'        => $sourceQuote->quote_discount_percent,
            'item'           => 0.0,
            'items_subtotal' => $itemsSubtotal,
        ];

        // Update target quote with discount
        Quote::query()->where('quote_id', $targetId)->update([
            'quote_discount_percent' => $globalDiscount['percent'],
            'quote_discount_amount'  => $globalDiscount['amount'],
        ]);

        // Copy quote items
        foreach ($sourceQuote->items as $item) {
            QuoteItem::create([
                'quote_id'             => $targetId,
                'item_tax_rate_id'     => $item->item_tax_rate_id,
                'item_product_id'      => $item->item_product_id,
                'item_name'            => $item->item_name,
                'item_description'     => $item->item_description,
                'item_quantity'        => $item->item_quantity,
                'item_price'           => $item->item_price,
                'item_discount_amount' => $item->item_discount_amount,
                'item_order'           => $item->item_order,
                'item_product_unit'    => $item->item_product_unit,
                'item_product_unit_id' => $item->item_product_unit_id,
            ]);
        }

        // Copy tax rates
        foreach ($sourceQuote->taxRates as $taxRate) {
            QuoteTaxRate::create([
                'quote_id'              => $targetId,
                'tax_rate_id'           => $taxRate->tax_rate_id,
                'include_item_tax'      => $taxRate->include_item_tax,
                'quote_tax_rate_amount' => $taxRate->quote_tax_rate_amount,
            ]);
        }

        // Copy custom fields
        $sourceCustom = QuoteCustom::query()->where('quote_id', $sourceId)->first();
        if ($sourceCustom) {
            $customData = $sourceCustom->toArray();
            unset($customData['quote_custom_id']);
            $customData['quote_id'] = $targetId;
            QuoteCustom::create($customData);
        }
    }

    /**
     * Get the due date based on creation date.
     *
     * @param string $quoteDateCreated
     *
     * @return string
     */
    public function calculateDateDue(string $quoteDateCreated): string
    {
        $expiresAfter = SettingsHelper::getSetting('quotes_expire_after');
        $expiryDate   = new DateTime($quoteDateCreated);
        $expiryDate->add(new DateInterval('P' . $expiresAfter . 'D'));

        return $expiryDate->format('Y-m-d');
    }

    /**
     * Generate a quote number.
     *
     * @param int $invoiceGroupId
     *
     * @return string
     */
    public function generateQuoteNumber(int $invoiceGroupId): string
    {
        $invoiceGroup = InvoiceGroup::findOrFail($invoiceGroupId);

        return app(InvoiceGroupService::class)->generateInvoiceNumber($invoiceGroup);
    }

    /**
     * Generate a unique URL key.
     *
     * @return string
     */
    public function generateUrlKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Get invoice group ID for a quote.
     *
     * @param int $quoteId
     *
     * @return int
     */
    public function getInvoiceGroupId(int $quoteId): int
    {
        $quote = Quote::findOrFail($quoteId);

        return $quote->invoice_group_id;
    }

    /**
     * Delete quote and cleanup orphans.
     *
     * @param int $quoteId
     *
     * @return bool|null
     */
    public function deleteQuote(int $quoteId): ?bool
    {
        $quote   = Quote::findOrFail($quoteId);
        $deleted = $quote->delete();

        // Cleanup orphaned records
        QuoteAmount::query()->where('quote_id', $quoteId)->delete();
        QuoteItem::query()->where('quote_id', $quoteId)->delete();
        QuoteTaxRate::query()->where('quote_id', $quoteId)->delete();
        QuoteCustom::query()->where('quote_id', $quoteId)->delete();

        return $deleted;
    }

    /**
     * Approve quote by URL key.
     *
     * @param string $quoteUrlKey
     *
     * @return int
     */
    public function approveQuoteByKey(string $quoteUrlKey): int
    {
        return Quote::query()->whereIn('quote_status_id', [2, 3])
            ->where('quote_url_key', $quoteUrlKey)
            ->update(['quote_status_id' => 4]);
    }

    /**
     * Get quote by URL key.
     *
     * @param string $urlKey
     *
     * @return Quote
     */
    public function getByUrlKey(string $urlKey): Quote
    {
        return Quote::query()->where('quote_url_key', $urlKey)->firstOrFail();
    }

    /**
     * Reject quote by URL key.
     *
     * @param string $quoteUrlKey
     *
     * @return int
     */
    public function rejectQuoteByKey(string $quoteUrlKey): int
    {
        return Quote::query()->whereIn('quote_status_id', [2, 3])
            ->where('quote_url_key', $quoteUrlKey)
            ->update(['quote_status_id' => 5]);
    }

    /**
     * Update a quote by ID.
     *
     * @param int   $quoteId
     * @param array $data
     *
     * @return int
     */
    public function updateQuote(int $quoteId, array $data): int
    {
        return Quote::query()->where('quote_id', $quoteId)->update($data);
    }

    /**
     * Approve quote by ID.
     *
     * @param int $quoteId
     *
     * @return int
     */
    public function approveQuoteById(int $quoteId): int
    {
        return Quote::query()->whereIn('quote_status_id', [2, 3])
            ->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 4]);
    }

    /**
     * Reject quote by ID.
     *
     * @param int $quoteId
     *
     * @return int
     */
    public function rejectQuoteById(int $quoteId): int
    {
        return Quote::query()->whereIn('quote_status_id', [2, 3])
            ->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 5]);
    }

    /**
     * Mark quote as viewed (only if currently sent).
     *
     * @param int $quoteId
     *
     * @return bool
     */
    public function markViewed(int $quoteId): bool
    {
        $quote = Quote::query()->select('quote_status_id')
            ->where('quote_id', $quoteId)
            ->first();

                if (!$quote || $quote->quote_status_id !== 2) {
            return false;
        }

        return Quote::query()->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 3]) > 0;
    }

    /**
     * Mark quote as sent (only if currently draft).
     *
     * @param int $quoteId
     *
     * @return bool
     */
    public function markSent(int $quoteId): bool
    {
        $quote = Quote::query()->select('quote_status_id')
            ->where('quote_id', $quoteId)
            ->first();

                if (!$quote || $quote->quote_status_id !== 1) {
            return false;
        }

        return Quote::query()->where('quote_id', $quoteId)
            ->update(['quote_status_id' => 2]) > 0;
    }

    /**
     * Generate quote number if applicable.
     *
     * @param int $quoteId
     *
     * @return void
     */
    public function generateQuoteNumberIfApplicable(int $quoteId): void
    {
        $quote = Quote::findOrFail($quoteId);

        // Generate new quote number if draft with no number and setting is off
        $generateForDraft = SettingsHelper::getSetting('generate_quote_number_for_draft');
        
        if ($quote->quote_status_id !== 1 || !empty($quote->quote_number) || $generateForDraft != 0) {
            return;
        }

        $quoteNumber = $this->generateQuoteNumber($quote->invoice_group_id);
        Quote::query()->where('quote_id', $quoteId)
            ->update(['quote_number' => $quoteNumber]);
    }

    /**
     * Find a quote with its relationships.
     *
     * @param int $id Quote ID
     * @param array $relations Relations to eager load
     *
     * @return Quote|null
     */
    public function findWithRelations(int $id, array $relations = ['client', 'user']): ?Quote
    {
        return Quote::query()->with($relations)->find($id);
    }

    /**
     * Find a quote with its relationships or fail.
     *
     * @param int $id Quote ID
     * @param array $relations Relations to eager load
     *
     * @return Quote
     */
    public function findWithRelationsOrFail(int $id, array $relations = ['client', 'user']): Quote
    {
        return Quote::query()->with($relations)->findOrFail($id);
    }

    /**
     * Get all quotes with relationships, ordered and filtered.
     *
     * @param array $relations Relations to eager load
     * @param string|null $status Status filter
     * @param int $perPage Number of items per page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllWithRelations(
        array $relations = ['client', 'user'],
        ?string $status = null,
        int $perPage = 15
    ) {
        $query = Quote::query()->with($relations);

        // Apply status filter using scopes
        switch ($status) {
            case 'draft':
                $query->draft();
                break;
            case 'sent':
                $query->sent();
                break;
            case 'viewed':
                $query->viewed();
                break;
            case 'approved':
                $query->approved();
                break;
            case 'rejected':
                $query->rejected();
                break;
            case 'canceled':
                $query->canceled();
                break;
            case 'all':
            default:
                // No filter for 'all'
                break;
        }

        return $query->orderBy('quote_date_created', 'desc')->paginate($perPage);
    }
}
