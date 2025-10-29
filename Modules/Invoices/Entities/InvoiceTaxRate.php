<?php

namespace Modules\Invoices\Entities;

use App\Models\BaseModel;

/**
 * InvoiceTaxRate Model
 * 
 * Eloquent model for managing ip_invoice_tax_rates
 * Migrated from CodeIgniter model
 */
class InvoiceTaxRate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_invoice_tax_rates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_tax_rate_id';

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
        'tax_rate_id',
        'include_item_tax',
        'invoice_tax_rate_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_tax_rate_id' => 'integer',
        'invoice_id' => 'integer',
        'tax_rate_id' => 'integer',
        'include_item_tax' => 'integer',
        'invoice_tax_rate_amount' => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the tax rate.
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
        return $this->belongsTo('Modules\Products\Entities\TaxRate', 'tax_rate_id', 'tax_rate_id');
    }

    /**
     * Get validation rules for invoice tax rates.
     *
     * @return array
     */
    public static function validationRules(): array
    {
        return [
            'invoice_id' => 'required|integer',
            'tax_rate_id' => 'required|integer',
            'include_item_tax' => 'required|integer',
        ];
    }

    /**
     * Save invoice tax rate and trigger calculations.
     * Only applicable in legacy calculation mode.
     *
     * @param array $data
     * @return InvoiceTaxRate|null
     */
    public static function saveTaxRate(array $data): ?InvoiceTaxRate
    {
        // Only applicable in legacy calculation mode
        if (!config_item('legacy_calculation')) {
            return null;
        }

        // Create or update the tax rate
        if (isset($data['invoice_tax_rate_id']) && $data['invoice_tax_rate_id']) {
            $taxRate = static::findOrFail($data['invoice_tax_rate_id']);
            $taxRate->update($data);
        } else {
            $taxRate = static::create($data);
        }

        // Recalculate invoice amounts if invoice_id is provided
        if (isset($data['invoice_id'])) {
            $globalDiscount = [
                'item' => InvoiceAmount::getGlobalDiscount($data['invoice_id']),
            ];
            InvoiceAmount::calculate($data['invoice_id'], $globalDiscount);
        }

        return $taxRate;
    }
}
