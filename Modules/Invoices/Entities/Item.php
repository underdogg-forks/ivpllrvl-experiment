<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * Item Model
 * 
 * Eloquent model for managing ip_invoice_items
 * Migrated from CodeIgniter model
 */
class Item extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_items';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'item_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'item_tax_rate_id',
        'item_product_id',
        'item_name',
        'item_description',
        'item_quantity',
        'item_price',
        'item_order',
        'item_discount_amount',
        'item_product_unit',
        'item_product_unit_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'item_id' => 'integer',
        'invoice_id' => 'integer',
        'item_tax_rate_id' => 'integer',
        'item_product_id' => 'integer',
        'item_quantity' => 'decimal:2',
        'item_price' => 'decimal:2',
        'item_order' => 'integer',
        'item_discount_amount' => 'decimal:2',
        'item_product_unit_id' => 'integer',
    ];

    /**
     * Get the invoice that owns the item.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Get the tax rate.
     */
    public function taxRate()
    {
        return $this->belongsTo('Modules\Products\Entities\TaxRate', 'item_tax_rate_id', 'tax_rate_id');
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo('Modules\Products\Entities\Product', 'item_product_id', 'product_id');
    }

    /**
     * Get the item amounts.
     */
    public function itemAmount()
    {
        return $this->hasOne('Modules\Invoices\Entities\Item_amount', 'item_id', 'item_id');
    }

    /**
     * Default ordering scope
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('item_order');
    }

    /**
     * Get validation rules for invoice items.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'invoice_id' => 'required|integer',
            'item_name' => 'required|string',
            'item_description' => 'nullable|string',
            'item_quantity' => 'nullable|numeric',
            'item_price' => 'nullable|numeric',
            'item_tax_rate_id' => 'nullable|integer',
            'item_product_id' => 'nullable|integer',
        ];
    }

    /**
     * Save invoice item and trigger calculations.
     *
     * @param array $data
     * @param array $globalDiscount
     * @return Item
     */
    public static function saveItem(array $data, array &$globalDiscount = []): Item
    {
        // Create or update the item
        if (isset($data['item_id']) && $data['item_id']) {
            $item = static::findOrFail($data['item_id']);
            $item->update($data);
        } else {
            $item = static::create($data);
        }

        // Calculate item amounts
        ItemAmount::calculate($item->item_id, $globalDiscount);

        // Recalculate invoice amounts
        if (isset($data['invoice_id'])) {
            InvoiceAmount::calculate($data['invoice_id'], $globalDiscount);
        }

        return $item;
    }

    /**
     * Delete invoice item and recalculate amounts.
     *
     * @param int $itemId
     * @return bool
     */
    public static function deleteItem(int $itemId): bool
    {
        // Get the item to find invoice_id
        $item = static::find($itemId);

        if (!$item) {
            return false;
        }

        $invoiceId = $item->invoice_id;

        // Delete the item
        $item->delete();

        // Delete the item amounts
        ItemAmount::where('item_id', $itemId)->delete();

        // Recalculate invoice amounts with global discount
        $globalDiscount = [
            'item' => InvoiceAmount::getGlobalDiscount($invoiceId),
        ];
        InvoiceAmount::calculate($invoiceId, $globalDiscount);

        return true;
    }

    /**
     * Get items subtotal for an invoice.
     *
     * @param int $invoiceId
     * @return float
     */
    public static function getItemsSubtotal(int $invoiceId): float
    {
        $result = \DB::table('ip_invoice_item_amounts')
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
