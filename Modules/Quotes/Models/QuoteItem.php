<?php

namespace Modules\Quotes\Entities;

use Modules\Core\Models\BaseModel;

/**
 * QuoteItem Model
 * 
 * Eloquent model for managing ip_quote_items
 * Migrated from CodeIgniter model
 */
class QuoteItem extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_quote_items';

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
        'quote_id',
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
        'quote_id' => 'integer',
        'item_tax_rate_id' => 'integer',
        'item_product_id' => 'integer',
        'item_quantity' => 'decimal:2',
        'item_price' => 'decimal:2',
        'item_order' => 'integer',
        'item_discount_amount' => 'decimal:2',
        'item_product_unit_id' => 'integer',
    ];

    /**
     * Get the quote that owns the item.
     */
    public function quote()
    {
        return $this->belongsTo('Modules\Quotes\Entities\Quote', 'quote_id', 'quote_id');
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
        return $this->hasOne('Modules\Quotes\Entities\QuoteItemAmount', 'item_id', 'item_id');
    }

    /**
     * Default ordering scope
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('item_order');
    }

    /**
     * Get validation rules for quote items.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'quote_id' => 'required|integer',
            'item_name' => 'required|string',
            'item_description' => 'nullable|string',
            'item_quantity' => 'nullable|numeric',
            'item_price' => 'nullable|numeric',
            'item_tax_rate_id' => 'nullable|integer',
            'item_product_id' => 'nullable|integer',
        ];
    }

    /**
     * Save quote item and trigger calculations.
     *
     * @param array $data
     * @param array $globalDiscount
     * @return QuoteItem
     */
    public static function saveItem(array $data, array &$globalDiscount = []): QuoteItem
    {
        // Create or update the item
        if (isset($data['item_id']) && $data['item_id']) {
            $item = static::findOrFail($data['item_id']);
            $item->update($data);
        } else {
            $item = static::create($data);
        }

        // Calculate item amounts
        QuoteItemAmount::calculate($item->item_id, $globalDiscount);

        // Recalculate quote amounts
        if (isset($data['quote_id'])) {
            QuoteAmount::calculate($data['quote_id'], $globalDiscount);
        }

        return $item;
    }

    /**
     * Delete quote item and recalculate amounts.
     *
     * @param int $itemId
     * @return bool
     */
    public static function deleteItem(int $itemId): bool
    {
        // Get the item to find quote_id
        $item = static::find($itemId);

        if (!$item) {
            return false;
        }

        $quoteId = $item->quote_id;

        // Delete the item
        $item->delete();

        // Delete the item amounts
        QuoteItemAmount::where('item_id', $itemId)->delete();

        // Recalculate quote amounts with global discount
        $globalDiscount = [
            'item' => QuoteAmount::getGlobalDiscount($quoteId),
        ];
        QuoteAmount::calculate($quoteId, $globalDiscount);

        return true;
    }

    /**
     * Get items subtotal for a quote.
     *
     * @param int $quoteId
     * @return float
     */
    public static function getItemsSubtotal(int $quoteId): float
    {
        $result = \DB::table('ip_quote_item_amounts')
            ->selectRaw('SUM(item_subtotal) AS items_subtotal')
            ->whereIn('item_id', function ($query) use ($quoteId) {
                $query->select('item_id')
                    ->from('ip_quote_items')
                    ->where('quote_id', $quoteId);
            })
            ->first();

        return (float) ($result->items_subtotal ?? 0.0);
    }
}
