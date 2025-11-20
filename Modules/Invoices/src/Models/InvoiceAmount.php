<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * InvoiceAmount Model.
 *
 * Eloquent model for managing ip_invoice_amounts
 * Stores calculated totals for invoices
 * Migrated from CodeIgniter Mdl_Invoice_Amounts
 */
class InvoiceAmount extends BaseModel
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
    protected $table = 'ip_invoice_amounts';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'invoice_amount_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',
        'invoice_item_subtotal',
        'invoice_item_tax_total',
        'invoice_tax_total',
        'invoice_total',
        'invoice_paid',
        'invoice_balance',
        'invoice_item_discount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'invoice_amount_id'      => 'integer',
        'invoice_id'             => 'integer',
        'invoice_item_subtotal'  => 'decimal:2',
        'invoice_item_tax_total' => 'decimal:2',
        'invoice_tax_total'      => 'decimal:2',
        'invoice_total'          => 'decimal:2',
        'invoice_paid'           => 'decimal:2',
        'invoice_balance'        => 'decimal:2',
        'invoice_item_discount'  => 'decimal:2',
    ];

    /**
     * Get the invoice that owns the amount.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Models\Invoice', 'invoice_id', 'invoice_id');
    }
}
