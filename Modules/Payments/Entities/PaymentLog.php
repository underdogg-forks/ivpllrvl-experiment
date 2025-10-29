<?php

namespace Modules\Payments\Entities;

use App\Models\BaseModel;

/**
 * PaymentLog Model
 * 
 * Eloquent model for managing ip_merchant_responses
 * Migrated from CodeIgniter model
 */
class PaymentLog extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ip_merchant_responses';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'merchant_response_id';

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
        'merchant_response_data',
        'merchant_response_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'merchant_response_id' => 'integer',
        'invoice_id' => 'integer',
    ];

    /**
     * Get the invoice that owns the log.
     */
    public function invoice()
    {
        return $this->belongsTo('Modules\Invoices\Entities\Invoice', 'invoice_id', 'invoice_id');
    }

    /**
     * Default ordering scope
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('merchant_response_id', 'desc');
    }
}
