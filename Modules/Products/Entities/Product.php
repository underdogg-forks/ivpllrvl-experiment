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
        'provider_name',
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
        return $this->belongsTo('Modules\Products\Entities\Tax_rate', 'tax_rate_id', 'tax_rate_id');
    }

    /**
     * Get the unit.
     */
    public function unit()
    {
        return $this->belongsTo('Modules\Products\Entities\Unit', 'unit_id', 'unit_id');
    }

    /**
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->join('ip_families', 'ip_families.family_id', '=', 'ip_products.family_id', 'left')
            ->orderBy('ip_families.family_name')
            ->orderBy('ip_products.product_name');
    }

    /**
     * Search scope for products
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('product_sku', 'like', "%{$search}%")
              ->orWhere('product_name', 'like', "%{$search}%")
              ->orWhere('product_description', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by family scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $familyId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByFamily($query, $familyId)
    {
        return $query->where('family_id', $familyId);
    }

    /**
     * Mutator for product_price
     */
    public function setProductPriceAttribute($value)
    {
        $this->attributes['product_price'] = empty($value) ? null : standardize_amount($value);
    }

    /**
     * Mutator for purchase_price
     */
    public function setPurchasePriceAttribute($value)
    {
        $this->attributes['purchase_price'] = empty($value) ? null : standardize_amount($value);
    }
}
