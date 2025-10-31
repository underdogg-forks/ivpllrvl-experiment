<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * ItemAmount Model.
 *
 * Eloquent model for managing invoice item amounts
 * Migrated from CodeIgniter model
 */
class ItemAmount extends BaseModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_item_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'item_amount_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'item_subtotal',
        'item_tax_total',
        'item_discount',
        'item_total',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'item_amount_id' => 'integer',
        'item_id'        => 'integer',
        'item_subtotal'  => 'decimal:2',
        'item_tax_total' => 'decimal:2',
        'item_discount'  => 'decimal:2',
        'item_total'     => 'decimal:2',
    ];

    /**
     * Calculate invoice item amounts.
     *
     * @param int   $itemId
     * @param array $globalDiscount
     *
     * @return void
     */
    public static function calculate(int $itemId, array &$globalDiscount = []): void
    {
        // Get the item with tax rate
        $item = Item::with('taxRate')->findOrFail($itemId);

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
            if ( ! isset($globalDiscount['item'])) {
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

        static::updateOrCreate(
            ['item_id' => $itemId],
            $dbArray
        );
    }

    /**
     * Get the item that owns the amount.
     */
    public function item()
    {
        return $this->belongsTo('Modules\Invoices\Models\Item', 'item_id', 'item_id');
    }
}
