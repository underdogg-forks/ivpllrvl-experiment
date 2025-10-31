<?php

namespace Modules\Invoices\Services;

use DB;
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
        $result = DB::table('ip_invoice_item_amounts')
            ->selectRaw('SUM(item_subtotal) AS items_subtotal')
            ->whereIn('item_id', function ($query) use ($invoiceId) {
                $query->select('item_id')
                    ->from('ip_invoice_items')
                    ->where('invoice_id', $invoiceId);
            })
            ->first();

        return (float) ($result->items_subtotal ?? 0.0);
    }
}
