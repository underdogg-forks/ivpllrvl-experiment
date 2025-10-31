<?php

namespace Modules\Quotes\Services;

use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteItemAmount;

/**
 * QuoteItemAmountService.
 *
 * Service class for managing quote item amount calculations
 * Extracted from QuoteItemAmount model
 */
class QuoteItemAmountService
{
    /**
     * Calculate quote item amounts.
     *
     * @param int   $itemId
     * @param array $globalDiscount
     *
     * @return void
     */
    public function calculate(int $itemId, array &$globalDiscount = []): void
    {
        // Get the item with tax rate
        $item = QuoteItem::with('taxRate')->findOrFail($itemId);

        // Calculate item subtotal
        $itemSubtotal = $item->item_quantity * $item->item_price;

        $legacyCalculation = config_item('legacy_calculation');

        if ($legacyCalculation) {
            // Legacy calculation mode
            $itemTaxTotal      = $itemSubtotal * (($item->taxRate->tax_rate_percent ?? 0) / 100);
            $itemDiscountTotal = $item->item_discount_amount * $item->item_quantity;
            $itemTotal         = $itemSubtotal + $itemTaxTotal - $itemDiscountTotal;
        } else {
            // New calculation mode - proportional discount distribution
            $itemDiscount = 0.0;

            // Apply global amount discount proportionally
            if (($globalDiscount['amount'] ?? 0) != 0 && ($globalDiscount['items_subtotal'] ?? 0) != 0) {
                $itemDiscount = round(
                    $globalDiscount['amount'] * ($itemSubtotal / $globalDiscount['items_subtotal']),
                    2
                );
            }

            // Apply global percent discount
            if (($globalDiscount['percent'] ?? 0) != 0) {
                $itemDiscount = round(($itemSubtotal * ($globalDiscount['percent'] / 100)), 2);
            }

            // Add to global discount tracking
            if (! isset($globalDiscount['item'])) {
                $globalDiscount['item'] = 0.0;
            }
            $globalDiscount['item'] += $itemDiscount;

            // Calculate with item-level discount
            $itemDiscountTotal = $item->item_discount_amount * $item->item_quantity;

            // Tax after all discounts
            $itemTaxTotal = ($itemSubtotal - $itemDiscount - $itemDiscountTotal)
                * (($item->taxRate->tax_rate_percent ?? 0) / 100);

            $itemTotal = $itemSubtotal - $itemDiscount - $itemDiscountTotal + $itemTaxTotal;
        }

        // Save or update item amounts
        $dbArray = [
            'item_id'        => $itemId,
            'item_subtotal'  => $itemSubtotal,
            'item_tax_total' => $itemTaxTotal,
            'item_discount'  => $itemDiscountTotal,
            'item_total'     => $itemTotal,
        ];

        QuoteItemAmount::updateOrCreate(
            ['item_id' => $itemId],
            $dbArray
        );
    }
}
