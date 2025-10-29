<?php

namespace Modules\Products\Entities;

use App\Models\BaseModel;

/**
 * Product Model
 * 
 * Eloquent model for managing products
 * Migrated from CodeIgniter Mdl_Products
 */
class Product extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_products';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'product_id';

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
        'family_id',
        'product_sku',
        'product_name',
        'product_description',
        'product_price',
        'purchase_price',
        'tax_rate_id',
        'unit_id',
        'product_tariff',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'product_id' => 'integer',
        'family_id' => 'integer',
        'tax_rate_id' => 'integer',
        'unit_id' => 'integer',
        'product_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
    ];

    /**
     * Get the family that owns the product.
     */
    public function family()
    {
        return $this->belongsTo('Modules\Products\Entities\Family', 'family_id', 'family_id');
    }

    /**
     * Get the tax rate.
     */
    public function taxRate()
    {
        return $this->belongsTo('Modules\Products\Entities\TaxRate', 'tax_rate_id', 'tax_rate_id');
    }

    /**
     * Get the unit.
     */
    public function unit()
    {
        return $this->belongsTo('Modules\Products\Entities\Unit', 'unit_id', 'unit_id');
    }
}
