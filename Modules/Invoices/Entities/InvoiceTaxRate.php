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
}
