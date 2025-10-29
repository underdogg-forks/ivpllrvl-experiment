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
        return $this->belongsTo('Modules\Products\Entities\Tax_rate', 'item_tax_rate_id', 'tax_rate_id');
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
}
