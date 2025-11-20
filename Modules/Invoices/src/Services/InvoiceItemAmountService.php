<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\ItemAmount;

class InvoiceItemAmountService
{
    public function calculate(int $itemId, array &$globalDiscount = []): void
    {
        $item = Item::query()->with('taxRate')->findOrFail($itemId);

        $itemSubtotal = $item->item_quantity * $item->item_price;

        $legacyCalculation = config_item('legacy_calculation');

        if ($legacyCalculation) {
            $itemTaxTotal      = $itemSubtotal * (($item->taxRate->tax_rate_percent ?? 0) / 100);
            $itemDiscountTotal = $item->item_discount_amount * $item->item_quantity;
            $itemTotal         = $itemSubtotal + $itemTaxTotal - $itemDiscountTotal;
        } else {
            $itemDiscount = 0.0;

            if (($globalDiscount['amount'] ?? 0) != 0 && ($globalDiscount['items_subtotal'] ?? 0) != 0) {
                $itemDiscount = round(
                    $globalDiscount['amount'] * ($itemSubtotal / $globalDiscount['items_subtotal']),
                    2
                );
            }

            if (($globalDiscount['percent'] ?? 0) != 0) {
                $itemDiscount = round(($itemSubtotal * ($globalDiscount['percent'] / 100)), 2);
            }

            if ( ! isset($globalDiscount['item'])) {
                $globalDiscount['item'] = 0.0;
            }
            $globalDiscount['item'] += $itemDiscount;

            $itemDiscountTotal = $item->item_discount_amount * $item->item_quantity;

            $itemTaxTotal = ($itemSubtotal - $itemDiscount - $itemDiscountTotal)
                * (($item->taxRate->tax_rate_percent ?? 0) / 100);

            $itemTotal = $itemSubtotal - $itemDiscount - $itemDiscountTotal + $itemTaxTotal;
        }

        $dbArray = [
            'item_id'        => $itemId,
            'item_subtotal'  => $itemSubtotal,
            'item_tax_total' => $itemTaxTotal,
            'item_discount'  => $itemDiscountTotal,
            'item_total'     => $itemTotal,
        ];

        ItemAmount::updateOrCreate(
            ['item_id' => $itemId],
            $dbArray
        );
    }
}
