<?php

namespace Modules\Quotes\Models;

use Modules\Core\Models\BaseModel;

/**
 * QuoteTaxRate Model.
 *
 * Eloquent model for managing ip_quote_tax_rates
 * Migrated from CodeIgniter model
 */
class QuoteTaxRate extends BaseModel
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
    protected $table = 'ip_quote_tax_rates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'quote_tax_rate_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quote_id',
        'tax_rate_id',
        'include_item_tax',
        'quote_tax_rate_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'quote_tax_rate_id'     => 'integer',
        'quote_id'              => 'integer',
        'tax_rate_id'           => 'integer',
        'include_item_tax'      => 'integer',
        'quote_tax_rate_amount' => 'decimal:2',
    ];

    /**
     * Get validation rules for quote tax rates.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'quote_id'         => 'required|integer',
            'tax_rate_id'      => 'required|integer',
            'include_item_tax' => 'required|integer',
        ];
    }

    /**
     * Save quote tax rate and trigger calculations.
     * Only applicable in legacy calculation mode.
     *
     * @param array $data
     *
     * @return QuoteTaxRate|null
     */
    public static function saveTaxRate(array $data): ?self
    {
        // Only applicable in legacy calculation mode
        if ( ! config_item('legacy_calculation')) {
            return null;
        }

        // Create or update the tax rate
        if (isset($data['quote_tax_rate_id']) && $data['quote_tax_rate_id']) {
            $taxRate = static::findOrFail($data['quote_tax_rate_id']);
            $taxRate->update($data);
        } else {
            $taxRate = static::create($data);
        }

        // Recalculate quote amounts if quote_id is provided
        if (isset($data['quote_id'])) {
            $globalDiscount = [
                'item' => QuoteAmount::getGlobalDiscount($data['quote_id']),
            ];
            QuoteAmount::calculate($data['quote_id'], $globalDiscount);
        }

        return $taxRate;
    }

    /**
     * Get the quote that owns the tax rate.
     */
    public function quote()
    {
        return $this->belongsTo('Modules\Quotes\Models\Quote', 'quote_id', 'quote_id');
    }

    /**
     * Get the tax rate.
     */
    public function taxRate()
    {
        return $this->belongsTo('Modules\Products\Models\TaxRate', 'tax_rate_id', 'tax_rate_id');
    }
}
