<?php

namespace Modules\Quotes\Services;

use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteItemAmount;

/**
 * QuoteItemService.
 *
 * Service class for managing quote items business logic
 * Extracted from QuoteItem model
 */
class QuoteItemService
{
    /**
     * QuoteAmountService instance.
     *
     * @var QuoteAmountService
     */
    protected QuoteAmountService $quoteAmountService;

    /**
     * QuoteItemAmountService instance.
     *
     * @var QuoteItemAmountService
     */
    protected QuoteItemAmountService $quoteItemAmountService;

    /**
     * Constructor.
     *
     * @param QuoteAmountService     $quoteAmountService
     * @param QuoteItemAmountService $quoteItemAmountService
     */
    public function __construct(
        QuoteAmountService $quoteAmountService,
        QuoteItemAmountService $quoteItemAmountService
    ) {
        $this->quoteAmountService     = $quoteAmountService;
        $this->quoteItemAmountService = $quoteItemAmountService;
    }

    /**
     * Get validation rules for quote items.
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        return [
            'quote_id'         => 'required|integer',
            'item_name'        => 'required|string',
            'item_description' => 'nullable|string',
            'item_quantity'    => 'nullable|numeric',
            'item_price'       => 'nullable|numeric',
            'item_tax_rate_id' => 'nullable|integer',
            'item_product_id'  => 'nullable|integer',
        ];
    }

    /**
     * Save quote item and trigger calculations.
     *
     * @param array $data
     * @param array $globalDiscount
     *
     * @return QuoteItem
     */
    public function saveItem(array $data, array &$globalDiscount = []): QuoteItem
    {
        // Create or update the item
        if (isset($data['item_id']) && $data['item_id']) {
            $item = QuoteItem::findOrFail($data['item_id']);
            $item->update($data);
        } else {
            $item = QuoteItem::create($data);
        }

        // Calculate item amounts
        $this->quoteItemAmountService->calculate($item->item_id, $globalDiscount);

        // Recalculate quote amounts
        if (isset($data['quote_id'])) {
            $this->quoteAmountService->calculate($data['quote_id'], $globalDiscount);
        }

        return $item;
    }

    /**
     * Delete quote item and recalculate amounts.
     *
     * @param int $itemId
     *
     * @return bool
     */
    public function deleteItem(int $itemId): bool
    {
        // Get the item to find quote_id
        $item = QuoteItem::find($itemId);

        if (! $item) {
            return false;
        }

        $quoteId = $item->quote_id;

        // Delete the item
        $item->delete();

        // Delete the item amounts
        QuoteItemAmount::where('item_id', $itemId)->delete();

        // Recalculate quote amounts with global discount
        $globalDiscount = [
            'item' => $this->quoteAmountService->getGlobalDiscount($quoteId),
        ];
        $this->quoteAmountService->calculate($quoteId, $globalDiscount);

        return true;
    }

    /**
     * Get items subtotal for a quote.
     *
     * @param int $quoteId
     *
     * @return float
     */
    public function getItemsSubtotal(int $quoteId): float
    {
        // Get all item IDs for this quote
        $itemIds = QuoteItem::where('quote_id', $quoteId)
            ->pluck('item_id');

        // Sum the subtotals from quote_item_amounts
        $result = QuoteItemAmount::whereIn('item_id', $itemIds)
            ->sum('item_subtotal');

        return (float) ($result ?? 0.0);
    }
}
