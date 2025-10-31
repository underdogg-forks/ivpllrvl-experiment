<?php

namespace Modules\Invoices\Models;

use Modules\Core\Models\BaseModel;

/**
 * InvoiceSumex Model.
 *
 * Eloquent model for managing ip_invoice_sumex
 * Migrated from CodeIgniter model
 */
class InvoiceSumex extends BaseModel
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
    protected $table = 'ip_invoice_sumex';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'sumex_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sumex_invoice',
        'sumex_reason',
        'sumex_diagnosis',
        'sumex_observations',
        'sumex_treatmentstart',
        'sumex_treatmentend',
        'sumex_casedate',
        'sumex_casenumber',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sumex_id'      => 'integer',
        'sumex_invoice' => 'integer',
        'sumex_reason'  => 'integer',
    ];

    /**
     * Get the invoice that owns the sumex data.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Models\Invoice', 'sumex_invoice', 'invoice_id');
    }
}
