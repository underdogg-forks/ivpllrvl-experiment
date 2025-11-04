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

        ItemAmount::query()->where('item_id', $itemId)->delete();

        $globalDiscount = [
            'item' => app(InvoiceAmountService::class)->getGlobalDiscount($invoiceId),
        ];
        app(InvoiceAmountService::class)->calculate($invoiceId, $globalDiscount);

        return true;
    }

    public function getItemsSubtotal(int $invoiceId): float
    {
        // Get all item IDs for this invoice
        $itemIds = Item::query()->where('invoice_id', $invoiceId)
            ->pluck('item_id');

        // Sum the subtotals from invoice_item_amounts
        $result = ItemAmount::query()->whereIn('item_id', $itemIds)
            ->sum('item_subtotal');

        return (float) ($result ?? 0.0);
    }

    /**
     * Get invoice items by invoice ID.
     *
     * @param int $invoiceId
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getItemsByInvoiceId(int $invoiceId)
    {
        return Item::query()->where('invoice_id', $invoiceId)->orderBy('item_order')->get();
    }


    /**
     * Find an item by invoice ID and item ID.
     *
     * @param int $invoiceId
     * @param int $itemId
     *
     * @return Item|null
     */
    public function findByInvoiceAndItemId(int $invoiceId, int $itemId): ?Item
    {
        return Item::query()->where('invoice_id', $invoiceId)
            ->where('item_id', $itemId)
            ->first();
    }
}
