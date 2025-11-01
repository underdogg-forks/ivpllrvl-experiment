<?php

namespace Modules\Invoices\Services;

use Modules\Invoices\Models\Item;
use Modules\Invoices\Models\ItemAmount;

class InvoiceItemService
{
    public function getValidationRules(): array
    {
        return [
            'invoice_id'       => 'required|integer',
            'item_name'        => 'required|string',
            'item_description' => 'nullable|string',
            'item_quantity'    => 'nullable|numeric',
            'item_price'       => 'nullable|numeric',
            'item_tax_rate_id' => 'nullable|integer',
            'item_product_id'  => 'nullable|integer',
        ];
    }

    public function saveItem(?int $itemId, array $data, int $invoiceId, array &$globalDiscount = []): Item
    {
        $payload = array_merge($data, ['invoice_id' => $invoiceId]);

        if ($itemId) {
            $item = Item::findOrFail($itemId);
            $item->update($payload);
        } else {
            $item = Item::create($payload);
        }

        app(InvoiceItemAmountService::class)->calculate($item->item_id, $globalDiscount);
        app(InvoiceAmountService::class)->calculate($invoiceId, $globalDiscount);

        return $item;
    }

    public function deleteItem(int $itemId): bool
    {
        $item = Item::find($itemId);

        if (! $item) {
            return false;
        }

        $invoiceId = $item->invoice_id;
        $item->delete();

        ItemAmount::where('item_id', $itemId)->delete();

        $globalDiscount = [
            'item' => app(InvoiceAmountService::class)->getGlobalDiscount($invoiceId),
        ];
        app(InvoiceAmountService::class)->calculate($invoiceId, $globalDiscount);

        return true;
    }

    public function getItemsSubtotal(int $invoiceId): float
    {
        // Get all item IDs for this invoice
        $itemIds = Item::where('invoice_id', $invoiceId)
            ->pluck('item_id');

        // Sum the subtotals from invoice_item_amounts
        $result = ItemAmount::whereIn('item_id', $itemIds)
            ->sum('item_subtotal');

        return (float) ($result ?? 0.0);
    }
}
